<?php

namespace Tests\Feature;

use App\Models\SupportTicket;
use App\Models\User;
use App\Services\RootFlashTicketClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.rootflash.tickets' => [
            'api_url' => 'https://rootflash.test',
            'identifier' => 'physiosource',
            'key_id' => 'test-key-id',
            'secret' => 'test-secret',
            'client_reference' => 'physiosource',
            'client_email' => 'support@physiosource.test',
        ]]);
    }

    public function test_guest_must_sign_in_to_view_support_tickets(): void
    {
        $this->get(route('admin.help.support-ticket'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_regular_client_cannot_access_admin_ticket_routes_or_frontend_ticket_urls(): void
    {
        $client = User::factory()->create();

        $this->actingAs($client)
            ->get(route('admin.help.support-ticket'))
            ->assertRedirect(route('admin.login'));
        $this->get('/support-tickets')->assertNotFound();
    }

    public function test_admin_ticket_pages_handle_uninstalled_ticket_tables_without_query_exceptions(): void
    {
        Schema::shouldReceive('hasTable')->andReturn(false);
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.help.support-ticket'))
            ->assertOk()
            ->assertSee('Support ticket storage is not installed.')
            ->assertDontSee('Raise Support Ticket');

        $this->get(route('admin.help.support-ticket.create'))
            ->assertOk()
            ->assertSee('Ticket submission is unavailable.')
            ->assertDontSee('name="title"', false);

        $this->post(route('admin.help.support-ticket.store'), [])
            ->assertRedirect(route('admin.help.support-ticket.create'))
            ->assertSessionHas('error');

        $this->get(route('admin.help.support-ticket.show', 1))
            ->assertStatus(503);
    }

    public function test_rootflash_requires_https_except_for_loopback_development(): void
    {
        $client = app(RootFlashTicketClient::class);

        config(['services.rootflash.tickets.api_url' => 'http://rootflash.example.test']);
        $this->assertFalse($client->isConfigured());

        config(['services.rootflash.tickets.api_url' => 'http://127.0.0.1:8000']);
        $this->assertTrue($client->isConfigured());

        config(['services.rootflash.tickets.api_url' => 'https://rootflash.example.test']);
        $this->assertTrue($client->isConfigured());
    }

    public function test_invalid_attachment_is_rejected_without_creating_a_ticket(): void
    {
        Storage::fake('local');
        $user = $this->createAdminUser();

        $this->actingAs($user, 'admin')
            ->post(route('admin.help.support-ticket.store'), [
                'source_reference' => (string) Str::uuid(),
                'title' => 'Unsupported attachment',
                'description' => 'This file type is not accepted.',
                'category' => 'Other',
                'priority' => 'medium',
                'attachments' => [UploadedFile::fake()->create('payload.txt', 10, 'text/plain')],
            ])
            ->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseCount('support_tickets', 0);
    }

    public function test_authenticated_user_creates_ticket_with_private_attachment_and_signed_idempotent_request(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([
            'rootflash.test/*' => Http::response([
                'data' => [
                    'ticket_id' => 'RF-PHYSIO-1',
                    'external_reference' => 'ignored',
                    'status' => 'open',
                    'priority' => 'high',
                    'created_at' => now()->toISOString(),
                ],
                'replayed' => false,
            ], 201),
        ]);
        $user = $this->createAdminUser([
            'name' => 'Physio User',
            'email' => 'physio-user@example.test',
        ]);
        $sourceReference = (string) Str::uuid();

        $response = $this->actingAs($user, 'admin')->post(route('admin.help.support-ticket.store'), [
            'source_reference' => $sourceReference,
            'title' => 'Website sign-in is failing',
            'description' => 'The sign-in page shows an error.',
            'category' => 'Website issue',
            'priority' => 'high',
            'attachments' => [UploadedFile::fake()->image('signin-error.png')],
            'client_reference' => 'untrusted-client',
            'project_reference' => 'untrusted-project',
        ]);

        $ticket = SupportTicket::query()->sole();
        $response->assertRedirect(route('admin.help.support-ticket.show', $ticket));
        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'user_id' => $user->id,
            'requester_name' => 'Physio User',
            'requester_email' => 'physio-user@example.test',
            'source_reference' => $sourceReference,
            'rootflash_ticket_id' => 'RF-PHYSIO-1',
            'category' => 'Website issue',
            'priority' => 'high',
            'sync_status' => 'synced',
        ]);
        Storage::disk('local')->assertExists($ticket->attachment_paths[0]['path']);
        $this->assertSame(
            'support_ticket_created',
            $user->notifications()->sole()->data['type'],
        );

        $sentRequest = Http::recorded()->first()[0];
        $parts = parse_url($sentRequest->url());
        $timestamp = (string) ($sentRequest->header('X-RootFlash-Timestamp')[0] ?? '');
        $signature = (string) ($sentRequest->header('X-RootFlash-Signature')[0] ?? '');
        $requestUri = ($parts['path'] ?? '').(isset($parts['query']) ? '?'.$parts['query'] : '');
        $canonicalRequest = implode("\n", [
            'POST',
            $requestUri,
            'physiosource',
            $timestamp,
            $sourceReference,
            hash('sha256', $sentRequest->body()),
        ]);
        $this->assertSame('POST', $sentRequest->method());
        $this->assertTrue($sentRequest->hasHeader('X-RootFlash-Key-Id', 'test-key-id'));
        $this->assertTrue($sentRequest->hasHeader('Idempotency-Key', $sourceReference));
        $this->assertSame(
            hash_hmac('sha256', $canonicalRequest, 'test-secret'),
            $signature,
        );
        $this->assertStringContainsString('metadata[source_user_id]', $sentRequest->body());
        $this->assertStringContainsString('metadata[source_user_email]', $sentRequest->body());
        $this->assertStringContainsString($user->email, $sentRequest->body());
        $this->assertStringNotContainsString('untrusted-client', $sentRequest->body());
        $this->assertStringNotContainsString('untrusted-project', $sentRequest->body());

        $this->actingAs($user, 'admin')->get(route('admin.help.support-ticket.show', $ticket))
            ->assertOk()
            ->assertSee('RF-PHYSIO-1')
            ->assertSee('Ticket RF-PHYSIO-1 was submitted and confirmed by the support desk.')
            ->assertSee('Synchronization with the RootFlash support desk is confirmed.');
    }

    public function test_repeated_submission_reference_does_not_create_a_second_local_ticket(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'rootflash.test/*' => Http::response([
                'data' => [
                    'ticket_id' => 'RF-PHYSIO-2',
                    'status' => 'open',
                    'priority' => 'medium',
                ],
                'replayed' => false,
            ], 201),
        ]);
        $user = $this->createAdminUser([
            'name' => 'Original Physio User',
            'email' => 'original@example.test',
        ]);
        $sourceReference = (string) Str::uuid();
        $payload = [
            'source_reference' => $sourceReference,
            'title' => 'Repeated report',
            'description' => 'Same request should reuse the original ticket.',
            'category' => 'Other',
            'priority' => 'medium',
        ];

        $this->actingAs($user, 'admin')->post(route('admin.help.support-ticket.store'), $payload)
            ->assertRedirect();
        $user->update([
            'name' => 'Changed Name',
            'email' => 'changed@example.test',
        ]);
        $this->post(route('admin.help.support-ticket.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseCount('support_tickets', 1);
        $this->assertDatabaseHas('support_tickets', [
            'source_reference' => $sourceReference,
            'requester_name' => 'Original Physio User',
            'requester_email' => 'original@example.test',
            'rootflash_ticket_id' => 'RF-PHYSIO-2',
        ]);
        $this->assertCount(2, Http::recorded());
        $this->assertSame(1, $user->notifications()->count());
        $this->assertSame(
            'Original Physio User',
            SupportTicket::query()->sole()->requester_name,
        );
    }

    public function test_ticket_page_refreshes_rootflash_status_and_public_replies(): void
    {
        $user = $this->createAdminUser();
        $ticket = $this->createTicket($user);
        Http::preventStrayRequests();
        Http::fake([
            'rootflash.test/*' => Http::response([
                'data' => [
                    'status' => 'in_progress',
                    'priority' => 'critical',
                    'updated_at' => now()->toISOString(),
                    'attachment_url' => 'https://rootflash.test/api/v1/integrations/tickets/'.$ticket->source_reference.'/attachments/ticket',
                    'replies' => [[
                        'body' => 'We are investigating this report.',
                        'created_at' => now()->toISOString(),
                        'attachment_url' => 'https://rootflash.test/api/v1/integrations/tickets/'.$ticket->source_reference.'/attachments/reply-42',
                    ], [
                        'is_public' => false,
                        'body' => 'Private response without an internal flag.',
                        'created_at' => now()->toISOString(),
                    ], [
                        'is_internal' => true,
                        'body' => 'Internal note: do not show this to the user.',
                        'created_at' => now()->toISOString(),
                    ]],
                ],
            ]),
        ]);

        $this->actingAs($user, 'admin')
            ->get(route('admin.help.support-ticket.show', $ticket))
            ->assertOk()
            ->assertSee('We are investigating this report.')
            ->assertSee('In Progress')
            ->assertSee('Critical')
            ->assertSee('Download RootFlash attachment')
            ->assertSee('Download reply attachment');
        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'source' => 'rootflash',
            'body' => 'We are investigating this report.',
            'is_public' => true,
        ]);
        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'body' => 'We are investigating this report.',
            'attachment_metadata' => json_encode(['reference' => 'reply-42']),
        ]);
        $this->assertDatabaseMissing('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'body' => 'Private response without an internal flag.',
        ]);
        $this->assertDatabaseMissing('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'body' => 'Internal note: do not show this to the user.',
        ]);

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
            'priority' => 'critical',
        ]);
        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
        ]);
        $this->get(route('admin.help.support-ticket.show', $ticket))->assertOk();
        $this->assertSame(1, $user->notifications()->where('data->type', 'support_ticket_reply')->count());
        $this->assertSame(1, $user->notifications()->where('data->type', 'support_ticket_status')->count());
    }

    public function test_only_authorized_admins_can_view_or_download_ticket_attachments(): void
    {
        Storage::fake('local');
        $owner = $this->createAdminUser();
        $otherAdmin = $this->createAdminUser();
        $path = UploadedFile::fake()->create('private-report.pdf')->store('support-tickets/'.$owner->id, 'local');
        $ticket = $this->createTicket($owner, [
            'attachment_paths' => [['path' => $path, 'name' => 'private-report.pdf']],
            'sync_status' => 'failed',
        ]);

        $this->actingAs($otherAdmin, 'admin')
            ->get(route('admin.help.support-ticket.show', $ticket))
            ->assertOk();
        $this->get(route('admin.help.support-ticket.attachments.download', [
            'ticket' => $ticket,
            'attachment' => 0,
        ]))->assertOk();
        $client = User::factory()->create();
        $this->actingAs($client, 'admin')->get(route('admin.help.support-ticket.show', $ticket))
            ->assertForbidden();
        $this->get(route('admin.help.support-ticket.attachments.download', [
            'ticket' => $ticket,
            'attachment' => 0,
        ]))->assertForbidden();
        $this->get(route('admin.help.support-ticket.rootflash-attachments.download', [
            'ticket' => $ticket,
            'attachment' => 'reply-42',
        ]))->assertForbidden();
    }

    public function test_failed_rootflash_request_keeps_the_ticket_and_shows_retry_state(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'rootflash.test/*' => Http::response(['message' => 'Unavailable'], 503),
        ]);
        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'admin')->post(route('admin.help.support-ticket.store'), [
            'source_reference' => (string) Str::uuid(),
            'title' => 'Cannot load a page',
            'description' => 'The page times out.',
            'category' => 'Website issue',
            'priority' => 'medium',
        ]);

        $ticket = SupportTicket::query()->sole();
        $response->assertRedirect(route('admin.help.support-ticket.show', $ticket));
        $response->assertSessionHas('success');
        $response->assertSessionHas('error', 'The RootFlash request failed. Your ticket is saved locally and can be retried.');
        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'sync_status' => 'failed',
        ]);
        $this->assertSame(1, $user->notifications()->where('data->type', 'support_ticket_created')->count());
        $this->get(route('admin.help.support-ticket.show', $ticket))
            ->assertOk()
            ->assertSee('Retry ticket synchronization')
            ->assertSee('has not synchronized with RootFlash');
    }

    public function test_admin_ticket_list_shows_website_tickets_with_status_and_public_reply(): void
    {
        $user = $this->createAdminUser();
        $otherUser = $this->createAdminUser();
        $openTicket = $this->createTicket($user, ['status' => 'open', 'sync_status' => 'failed']);
        $this->createTicket($user, ['status' => 'waiting_for_client', 'title' => 'Waiting on me']);
        $this->createTicket($user, ['status' => 'closed', 'title' => 'My closed ticket']);
        $this->createTicket($otherUser, ['title' => 'Another users ticket']);
        $openTicket->replies()->create([
            'source' => 'rootflash',
            'message_key' => hash('sha256', 'support-response'),
            'author_name' => 'Support',
            'body' => 'We have your report.',
            'is_public' => true,
            'sync_status' => 'synced',
        ]);

        $this->actingAs($user, 'admin')
            ->get(route('admin.help.support-ticket', ['status' => 'open']))
            ->assertOk()
            ->assertSee('Support Tickets')
            ->assertSee('Another users ticket')
            ->assertSee('Support replied')
            ->assertSee('Ticket reference')
            ->assertSee('Category')
            ->assertSee('Last updated');
        $this->get(route('admin.help.support-ticket', ['status' => 'waiting_for_client']))
            ->assertOk()
            ->assertSee('Waiting on me')
            ->assertSee('Waiting For Client')
            ->assertDontSee('My closed ticket');
    }

    public function test_clients_do_not_see_ticket_controls_and_admins_get_a_protected_ticket_form(): void
    {
        $client = User::factory()->create();
        $this->actingAs($client)
            ->get(route('support.help-center'))
            ->assertOk()
            ->assertDontSee('Raise Support Ticket')
            ->assertDontSee('My Support Tickets');

        $admin = $this->createAdminUser();
        $this->actingAs($admin, 'admin')
            ->get(route('admin.help.support-ticket.create'))
            ->assertOk()
            ->assertSee('Raise Support Ticket')
            ->assertSee('name="title"', false)
            ->assertSee('name="description"', false)
            ->assertSee('name="priority"', false)
            ->assertSee('name="attachments[]"', false)
            ->assertSee('Submit Ticket')
            ->assertDontSee('name="client_reference"', false)
            ->assertDontSee('name="project_reference"', false)
            ->assertDontSee('name="integration_secret"', false);
    }

    public function test_ticket_is_saved_with_clear_configuration_error_when_rootflash_is_not_configured(): void
    {
        Http::preventStrayRequests();
        config(['services.rootflash.tickets' => [
            'api_url' => null,
            'identifier' => null,
            'key_id' => null,
            'secret' => null,
            'client_reference' => null,
            'client_email' => null,
        ]]);
        $user = $this->createAdminUser();

        $response = $this->actingAs($user, 'admin')->post(route('admin.help.support-ticket.store'), [
            'source_reference' => (string) Str::uuid(),
            'title' => 'Saved without RootFlash',
            'description' => 'This ticket should stay available locally.',
            'category' => 'Other',
            'priority' => 'medium',
        ]);

        $ticket = SupportTicket::query()->sole();
        $response->assertRedirect(route('admin.help.support-ticket.show', $ticket))
            ->assertSessionHas('success', 'Ticket '.$ticket->source_reference.' was saved on PhysioSource. RootFlash synchronization is pending.')
            ->assertSessionHas('error', 'RootFlash is not configured. Your ticket is saved locally and can be retried after configuration.');
        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'user_id' => $user->id,
            'sync_status' => 'failed',
        ]);
        $this->get(route('admin.help.support-ticket.show', $ticket))
            ->assertOk()
            ->assertSee('Ticket '.$ticket->source_reference.' was saved on PhysioSource.')
            ->assertSee('RootFlash is not configured.')
            ->assertSee('Retry ticket synchronization');
    }

    public function test_admin_conversation_is_read_only_and_reply_submission_remains_in_rootflash(): void
    {
        $user = $this->createAdminUser();
        $ticket = $this->createTicket($user, ['sync_status' => 'failed']);
        $ticket->replies()->create([
            'source' => 'rootflash',
            'message_key' => hash('sha256', 'existing-public-reply'),
            'author_name' => 'RootFlash Support',
            'body' => 'This reply was saved from the public RootFlash conversation.',
            'is_public' => true,
            'sync_status' => 'synced',
        ]);
        $this->actingAs($user, 'admin')->get(route('admin.help.support-ticket.show', $ticket))
            ->assertOk()
            ->assertSee('Replies and status are managed in RootFlash Admin.')
            ->assertSee('This reply was saved from the public RootFlash conversation.')
            ->assertDontSee('Add a reply');
        $this->post('/admin/help/support-ticket/'.$ticket->id.'/replies', ['body' => 'No local reply API'])
            ->assertNotFound();
    }

    public function test_status_notifications_are_sent_once_when_a_status_change_is_refreshed(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'rootflash.test/*' => Http::response([
                'data' => [
                    'status' => 'resolved',
                    'updated_at' => '2026-11-17T10:00:00Z',
                    'replies' => [],
                ],
            ]),
        ]);
        $user = $this->createAdminUser();
        $ticket = $this->createTicket($user);

        $this->actingAs($user, 'admin')->get(route('admin.help.support-ticket.show', $ticket))
            ->assertOk()
            ->assertSee('Resolved')
            ->assertSee('Replies and status are managed in RootFlash Admin.');
        $this->get(route('admin.help.support-ticket.show', $ticket))->assertOk();

        $this->assertSame('resolved', $ticket->fresh()->status);
        $this->assertSame(1, $user->notifications()->where('data->type', 'support_ticket_status')->count());
    }

    public function test_detail_refresh_failure_keeps_saved_replies_visible(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'rootflash.test/*' => Http::response(['message' => 'Unavailable'], 503),
        ]);
        $user = $this->createAdminUser();
        $ticket = $this->createTicket($user);
        $ticket->replies()->create([
            'source' => 'user',
            'message_key' => hash('sha256', 'offline-user-reply'),
            'author_name' => $user->name,
            'body' => 'Please keep this reply while support is offline.',
            'is_public' => true,
            'sync_status' => 'pending',
        ]);

        $this->actingAs($user, 'admin')
            ->get(route('admin.help.support-ticket.show', $ticket))
            ->assertOk()
            ->assertSee('could not be loaded')
            ->assertSee('Please keep this reply while support is offline.');
    }

    public function test_rootflash_timeout_keeps_the_ticket_and_marks_sync_failed(): void
    {
        Http::preventStrayRequests();
        Http::fake(fn () => throw new ConnectionException('RootFlash request timed out.'));
        $user = $this->createAdminUser();

        $this->actingAs($user, 'admin')->post(route('admin.help.support-ticket.store'), [
            'source_reference' => (string) Str::uuid(),
            'title' => 'RootFlash timeout',
            'description' => 'The ticket should stay saved.',
            'category' => 'Other',
            'priority' => 'medium',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'title' => 'RootFlash timeout',
            'sync_status' => 'failed',
        ]);
    }

    public function test_signed_ticket_poll_and_attachment_download_match_rootflash_request_contract(): void
    {
        Http::preventStrayRequests();
        Http::fakeSequence('rootflash.test/*')
            ->push([
                'data' => [
                    'status' => 'open',
                    'updated_at' => now()->toISOString(),
                    'replies' => [],
                ],
            ])
            ->push('private attachment', 200, ['Content-Type' => 'application/pdf']);
        $user = $this->createAdminUser();
        $ticket = $this->createTicket($user);

        $this->actingAs($user, 'admin')
            ->get(route('admin.help.support-ticket.show', $ticket))
            ->assertOk();
        $this->get(route('admin.help.support-ticket.rootflash-attachments.download', [
            'ticket' => $ticket,
            'attachment' => 'ticket',
        ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('private attachment');

        $requests = Http::recorded()->map(fn (array $record): Request => $record[0]);
        $this->assertCount(2, $requests);

        foreach ($requests as $request) {
            $parts = parse_url($request->url());
            $timestamp = (string) ($request->header('X-RootFlash-Timestamp')[0] ?? '');
            $signature = (string) ($request->header('X-RootFlash-Signature')[0] ?? '');
            $requestUri = ($parts['path'] ?? '').(isset($parts['query']) ? '?'.$parts['query'] : '');
            $canonicalRequest = implode("\n", [
                'GET',
                $requestUri,
                'physiosource',
                $timestamp,
                '',
                hash('sha256', ''),
            ]);

            $this->assertMatchesRegularExpression('/\A[0-9]+\z/', $timestamp);
            $this->assertLessThanOrEqual(300, abs(now()->timestamp - (int) $timestamp));
            $this->assertSame(
                hash_hmac('sha256', $canonicalRequest, 'test-secret'),
                $signature,
            );
        }

        $this->assertSame(
            '/api/v1/integrations/tickets/'.$ticket->source_reference,
            parse_url($requests[0]->url(), PHP_URL_PATH),
        );
        $this->assertSame(
            '/api/v1/integrations/tickets/'.$ticket->source_reference.'/attachments/ticket',
            parse_url($requests[1]->url(), PHP_URL_PATH),
        );
    }

    private function createAdminUser(array $attributes = []): User
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create($attributes);
        $admin->assignRole('admin');

        return $admin;
    }

    private function createTicket(User $user, array $attributes = []): SupportTicket
    {
        return $user->supportTickets()->create([
            'source_reference' => (string) Str::uuid(),
            'title' => 'A sample issue',
            'description' => 'A sample description.',
            'requester_name' => $user->name,
            'requester_email' => $user->email,
            'category' => 'Other',
            'priority' => 'medium',
            'status' => 'open',
            'submission_hash' => hash('sha256', Str::uuid()->toString()),
            'sync_status' => 'synced',
            ...$attributes,
        ]);
    }
}
