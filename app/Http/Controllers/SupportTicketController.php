<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\SupportTicketUpdated;
use App\Services\RootFlashTicketClient;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        if (! $this->ticketSchemaIsReady()) {
            return view('admin.help.support-ticket', [
                'tickets' => null,
                'selectedStatus' => '',
                'statuses' => SupportTicket::STATUSES,
                'rootFlashTicketsUrl' => $this->rootFlashTicketsUrl(),
                'ticketSchemaReady' => false,
            ]);
        }

        $validated = $request->validate([
            'status' => ['nullable', Rule::in(SupportTicket::STATUSES)],
        ]);

        $ticketsQuery = SupportTicket::query()
            ->with('latestPublicReply')
            ->latest('created_at');

        if (filled($validated['status'] ?? null)) {
            $ticketsQuery->where('status', $validated['status']);
        }

        return view('admin.help.support-ticket', [
            'tickets' => $ticketsQuery->paginate(15)->withQueryString(),
            'selectedStatus' => $validated['status'] ?? '',
            'statuses' => SupportTicket::STATUSES,
            'rootFlashTicketsUrl' => $this->rootFlashTicketsUrl(),
            'ticketSchemaReady' => true,
        ]);
    }

    public function create(): View
    {
        return view('admin.help.support-ticket-create', [
            'sourceReference' => (string) Str::uuid(),
            'categories' => $this->categories(),
            'priorities' => $this->priorities(),
            'rootFlashTicketsUrl' => $this->rootFlashTicketsUrl(),
            'ticketSchemaReady' => $this->ticketSchemaIsReady(),
        ]);
    }

    public function store(Request $request, RootFlashTicketClient $rootFlash): RedirectResponse
    {
        if (! $this->ticketSchemaIsReady()) {
            return redirect()->route('admin.help.support-ticket.create')
                ->with('error', $this->ticketSchemaMessage());
        }

        $validated = $request->validate([
            'source_reference' => ['required', 'uuid'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'category' => ['required', 'string', Rule::in($this->categories())],
            'priority' => ['required', Rule::in($this->priorities())],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);
        /** @var User $user */
        $user = $request->user();
        $files = $request->file('attachments', []) ?? [];
        $submissionHash = $this->submissionHash($validated, $files);
        $storedPaths = [];
        $wasCreated = false;

        try {
            $ticket = DB::transaction(function () use ($validated, $user, $files, $submissionHash, &$storedPaths, &$wasCreated): SupportTicket {
                User::query()->lockForUpdate()->findOrFail($user->id);

                $existingTicket = $user->supportTickets()
                    ->where('source_reference', $validated['source_reference'])
                    ->first();

                if ($existingTicket) {
                    abort_unless(
                        hash_equals($existingTicket->submission_hash, $submissionHash),
                        409,
                        'This ticket reference was already used with different details.',
                    );

                    return $existingTicket;
                }

                $attachments = [];
                foreach ($files as $file) {
                    $path = $file->store('support-tickets/'.$user->id, 'local');
                    if ($path === false) {
                        throw new RuntimeException('A support ticket attachment could not be stored.');
                    }

                    $storedPaths[] = $path;
                    $attachments[] = [
                        'path' => $path,
                        'name' => $file->getClientOriginalName(),
                    ];
                }

                $ticket = $user->supportTickets()->create([
                    'requester_name' => $user->name,
                    'requester_email' => $user->email,
                    'source_reference' => $validated['source_reference'],
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'category' => $validated['category'],
                    'priority' => $validated['priority'],
                    'attachment_paths' => $attachments,
                    'submission_hash' => $submissionHash,
                    'sync_status' => 'pending',
                ]);
                $wasCreated = true;

                return $ticket;
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                Storage::disk('local')->delete($storedPaths);
            }

            throw $exception;
        }

        $isSynced = $this->syncTicket($ticket, $rootFlash);
        $reference = $ticket->rootflash_ticket_id ?: $ticket->source_reference;
        if ($wasCreated) {
            $syncState = $isSynced ? 'confirmed' : 'pending';
            $this->notifyOnce(
                $user,
                $ticket,
                'created:'.$ticket->source_reference,
                'created',
                "Ticket {$reference} was saved. RootFlash synchronization is {$syncState}.",
            );
        }

        $response = redirect()->route('admin.help.support-ticket.show', $ticket)
            ->with(
                'success',
                $isSynced
                    ? "Ticket {$reference} was submitted and confirmed by the support desk."
                    : "Ticket {$reference} was saved on PhysioSource. RootFlash synchronization is pending.",
            );

        if (! $isSynced) {
            $response->with('error', $this->syncFailureMessage($rootFlash));
        }

        return $response;
    }

    public function show(Request $request, int $ticket, RootFlashTicketClient $rootFlash): View
    {
        $ticket = $this->findTicket($ticket);
        $this->authorizeTicket($request, $ticket);
        $remoteTicket = null;
        $refreshFailed = false;

        if ($ticket->sync_status === 'synced') {
            try {
                $remoteTicket = $rootFlash->showTicket($ticket->source_reference);
                $this->refreshFromRootFlash($ticket, $remoteTicket);
            } catch (ConnectionException|RequestException|RuntimeException $exception) {
                $this->logSyncFailure($ticket, $exception);
                $refreshFailed = true;
            }
        }

        $freshTicket = $ticket->fresh();

        return view('admin.help.support-ticket-show', [
            'ticket' => $freshTicket,
            'replies' => $freshTicket->replies()
                ->where('is_public', true)
                ->oldest('created_at')
                ->get(),
            'hasPendingUserReplies' => $freshTicket->replies()
                ->where('is_public', true)
                ->where('source', 'user')
                ->where('sync_status', '!=', 'synced')
                ->exists(),
            'rootFlashTicketAttachment' => is_string($remoteTicket['attachment_url'] ?? null)
                ? $this->attachmentReferenceFromUrl($remoteTicket['attachment_url'], $ticket->source_reference)
                : null,
            'refreshFailed' => $refreshFailed,
            'rootFlashTicketsUrl' => $this->rootFlashTicketsUrl(),
        ]);
    }

    public function retry(Request $request, int $ticket, RootFlashTicketClient $rootFlash): RedirectResponse
    {
        $ticket = $this->findTicket($ticket);
        $this->authorizeTicket($request, $ticket);

        if ($ticket->sync_status === 'synced') {
            return redirect()->route('admin.help.support-ticket.show', $ticket);
        }

        $isSynced = $this->syncTicket($ticket, $rootFlash);

        $response = redirect()->route('admin.help.support-ticket.show', $ticket)
            ->with(
                $isSynced ? 'success' : 'error',
                $isSynced
                    ? 'Your ticket has been sent to PhysioSource Support.'
                    : 'The ticket remains saved on PhysioSource and synchronization is still pending.',
            );

        if (! $isSynced) {
            $response->with('error', $this->syncFailureMessage($rootFlash));
        }

        return $response;
    }

    public function downloadAttachment(Request $request, int $ticket, int $attachment): StreamedResponse
    {
        $ticket = $this->findTicket($ticket);
        $this->authorizeTicket($request, $ticket);
        $file = $ticket->attachment_paths[$attachment] ?? null;

        abort_unless(
            is_array($file)
                && isset($file['path'], $file['name'])
                && Storage::disk('local')->exists($file['path']),
            404,
        );

        return Storage::disk('local')->download($file['path'], $file['name']);
    }

    public function downloadRootFlashAttachment(
        Request $request,
        int $ticket,
        string $attachment,
        RootFlashTicketClient $rootFlash,
    ): Response {
        $ticket = $this->findTicket($ticket);
        $this->authorizeTicket($request, $ticket);
        abort_unless($ticket->sync_status === 'synced', 404);
        abort_unless(preg_match('/\A(?:ticket|reply-[0-9]+)\z/', $attachment) === 1, 404);

        try {
            $remoteResponse = $rootFlash->downloadAttachment($ticket->source_reference, $attachment);
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            $this->logSyncFailure($ticket, $exception);
            abort(502, 'The support attachment could not be downloaded right now.');
        }

        $contentType = strtolower((string) $remoteResponse->header('Content-Type'));
        if (! in_array($contentType, [
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf',
        ], true)) {
            $contentType = 'application/octet-stream';
        }

        $extension = match ($contentType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            default => 'bin',
        };
        $filename = 'support-ticket-'.$ticket->id.'-'.$attachment.'.'.$extension;

        return response($remoteResponse->body(), 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * @return list<string>
     */
    private function categories(): array
    {
        return ['Website issue', 'Account access', 'Content or course', 'Payment', 'Other'];
    }

    /**
     * @return list<string>
     */
    private function priorities(): array
    {
        return ['low', 'medium', 'high', 'critical'];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, UploadedFile>  $files
     */
    private function submissionHash(array $validated, array $files): string
    {
        $attachmentHashes = array_map(
            fn (UploadedFile $file): string|false => hash_file('sha256', $file->getRealPath()),
            $files,
        );
        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'attachment_hashes' => $attachmentHashes,
        ];

        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
    }

    private function syncTicket(SupportTicket $ticket, RootFlashTicketClient $rootFlash): bool
    {
        $ticket->update(['sync_status' => 'pending']);

        try {
            $data = $rootFlash->createTicket([
                'external_reference' => $ticket->source_reference,
                'client_reference' => config('services.rootflash.tickets.client_reference'),
                'client_name' => $ticket->requester_name,
                'client_email' => config('services.rootflash.tickets.client_email'),
                'subject' => $ticket->title,
                'description' => $ticket->description,
                'priority' => $ticket->priority,
                'category' => $ticket->category,
                'metadata' => [
                    'source_application' => 'PhysioSource',
                    'source_category' => $ticket->category,
                    'source_user_id' => (string) $ticket->user_id,
                    'source_user_name' => $ticket->requester_name,
                    'source_user_email' => $ticket->requester_email,
                ],
            ], $ticket->attachment_paths ?? [], $ticket->source_reference);

            foreach (['ticket_id', 'status', 'priority'] as $requiredKey) {
                if (! isset($data[$requiredKey]) || ! is_string($data[$requiredKey])) {
                    throw new RuntimeException('RootFlash returned an incomplete ticket response.');
                }
            }

            $ticket->update([
                'rootflash_ticket_id' => $data['ticket_id'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'sync_status' => 'synced',
            ]);

            return true;
        } catch (ConnectionException|RequestException|RuntimeException $exception) {
            $ticket->update(['sync_status' => 'failed']);
            $this->logSyncFailure($ticket, $exception);

            return false;
        }
    }

    /**
     * RootFlash's signed detail endpoint excludes internal replies.
     *
     * @param  array<string, mixed>  $data
     */
    private function refreshFromRootFlash(SupportTicket $ticket, array $data): void
    {
        $previousStatus = $ticket->status;
        $remoteStatus = $data['status'] ?? null;
        $remotePriority = $data['priority'] ?? null;

        if (is_string($remoteStatus) && in_array($remoteStatus, SupportTicket::STATUSES, true)
            && $previousStatus !== $remoteStatus) {
            $ticket->status = $remoteStatus;
            $ticket->save();

            $statusVersion = (string) ($data['updated_at'] ?? now()->format('U.u'));
            $this->notifyOnce(
                $ticket->user,
                $ticket,
                'status:'.hash('sha256', $previousStatus.'>'.$remoteStatus.'|'.$statusVersion),
                'status',
                'Ticket '.($ticket->rootflash_ticket_id ?: $ticket->source_reference)
                    .' is now '.str_replace('_', ' ', $remoteStatus).'.',
            );
        }

        if (is_string($remotePriority) && $ticket->priority !== $remotePriority) {
            $ticket->priority = $remotePriority;
            $ticket->save();
        }

        $remoteReplies = $data['replies'] ?? [];
        if (! is_array($remoteReplies)) {
            return;
        }

        foreach ($remoteReplies as $remoteReply) {
            if (! is_array($remoteReply) || ! $this->isSafePublicReply($remoteReply)) {
                continue;
            }

            $body = $remoteReply['body'] ?? $remoteReply['message'] ?? null;
            if (! is_string($body) || trim($body) === '') {
                continue;
            }

            $author = $remoteReply['author_name']
                ?? (is_array($remoteReply['author'] ?? null) ? ($remoteReply['author']['name'] ?? null) : null)
                ?? 'PhysioSource Support';
            $createdAt = $remoteReply['created_at'] ?? null;
            try {
                $replyCreatedAt = is_string($createdAt) ? Carbon::parse($createdAt) : now();
            } catch (InvalidFormatException $exception) {
                Log::warning('RootFlash returned a public ticket reply with an invalid timestamp.', [
                    'ticket_id' => $ticket->id,
                ]);

                continue;
            }
            $messageKey = hash('sha256', json_encode([
                'id' => $remoteReply['id'] ?? null,
                'author' => $author,
                'created_at' => $createdAt,
                'body' => $body,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));
            $isUserReply = in_array(
                strtolower((string) ($remoteReply['author_type'] ?? $remoteReply['author_role'] ?? '')),
                ['user', 'customer', 'requester'],
                true,
            ) || (
                is_string($remoteReply['author_email'] ?? null)
                && strcasecmp($remoteReply['author_email'], $ticket->requester_email) === 0
            );
            $attachmentReference = $this->attachmentReferenceFromUrl(
                $remoteReply['attachment_url'] ?? null,
                $ticket->source_reference,
            );

            $reply = $ticket->replies()->firstOrCreate(
                ['message_key' => $messageKey],
                [
                    'source' => $isUserReply ? 'user' : 'rootflash',
                    'author_name' => is_string($author) ? $author : 'PhysioSource Support',
                    'body' => $body,
                    'is_public' => true,
                    'sync_status' => 'synced',
                    'attachment_metadata' => $attachmentReference
                        ? ['reference' => $attachmentReference]
                        : null,
                    'created_at' => $replyCreatedAt,
                ],
            );

            if ($reply->wasRecentlyCreated) {
                $ticket->touch();

                if ($reply->source === 'rootflash') {
                    $this->notifyOnce(
                        $ticket->user,
                        $ticket,
                        'reply:'.$messageKey,
                        'reply',
                        'PhysioSource Support replied to ticket '
                            .($ticket->rootflash_ticket_id ?: $ticket->source_reference).'.',
                    );
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $reply
     */
    private function isSafePublicReply(array $reply): bool
    {
        // Keep this defensive if the response contract ever includes visibility metadata.
        if (filter_var($reply['is_internal'] ?? false, FILTER_VALIDATE_BOOLEAN)
            || filter_var($reply['is_private'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        foreach (['is_public', 'public'] as $publicKey) {
            if (array_key_exists($publicKey, $reply)
                && ! filter_var($reply[$publicKey], FILTER_VALIDATE_BOOLEAN)) {
                return false;
            }
        }

        if (array_key_exists('visibility', $reply)
            && strtolower((string) $reply['visibility']) !== 'public') {
            return false;
        }

        return true;
    }

    private function attachmentReferenceFromUrl(mixed $url, string $externalReference): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path)
            || preg_match(
                '#\A/api/v1/integrations/tickets/([^/]+)/attachments/(ticket|reply-[0-9]+)\z#',
                $path,
                $matches,
            ) !== 1
            || rawurldecode($matches[1]) !== $externalReference) {
            return null;
        }

        return $matches[2];
    }

    private function notifyOnce(
        User $user,
        SupportTicket $ticket,
        string $eventKey,
        string $event,
        string $message,
    ): void {
        if (! $user->hasAnyRole(['super_admin', 'admin'])) {
            return;
        }

        if ($user->notifications()->where('data->event_key', $eventKey)->exists()) {
            return;
        }

        $user->notify(new SupportTicketUpdated($ticket, $eventKey, $event, $message));
    }

    private function authorizeTicket(Request $request, SupportTicket $ticket): void
    {
        abort_unless(
            $request->user('admin')?->hasAnyRole(['super_admin', 'admin']),
            403,
        );
    }

    private function rootFlashTicketsUrl(): ?string
    {
        $adminUrl = trim((string) config('services.rootflash.admin_url'));
        $parts = $adminUrl !== '' ? parse_url($adminUrl) : false;

        return is_array($parts)
            && in_array($parts['scheme'] ?? null, ['https', 'http'], true)
            && filled($parts['host'] ?? null)
                ? rtrim($adminUrl, '/').'/admin/tickets'
                : null;
    }

    private function ticketSchemaIsReady(): bool
    {
        return Schema::hasTable('support_tickets')
            && Schema::hasTable('support_ticket_replies');
    }

    private function findTicket(int $ticketId): SupportTicket
    {
        abort_unless($this->ticketSchemaIsReady(), 503, $this->ticketSchemaMessage());

        return SupportTicket::query()->findOrFail($ticketId);
    }

    private function ticketSchemaMessage(): string
    {
        return 'The support-ticket database tables are not installed. Ask your deployment administrator to apply the existing support-ticket migrations before using this page.';
    }

    private function logSyncFailure(SupportTicket $ticket, Throwable $exception): void
    {
        Log::warning('PhysioSource ticket could not sync with RootFlash.', [
            'ticket_id' => $ticket->id,
            'exception' => $exception::class,
        ]);
    }

    private function syncFailureMessage(RootFlashTicketClient $rootFlash): string
    {
        return $rootFlash->isConfigured()
            ? 'The RootFlash request failed. Your ticket is saved locally and can be retried.'
            : 'RootFlash is not configured. Your ticket is saved locally and can be retried after configuration.';
    }
}
