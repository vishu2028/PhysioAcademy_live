<?php

namespace Tests\Feature;

use App\Models\Doubt;
use App\Models\DoubtLike;
use App\Models\Subject;
use App\Models\TopicRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentSubmittedItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_menu_links_student_records_in_the_required_order(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('doubts.mine'));

        $response->assertOk()
            ->assertSee('Doubt Session History')
            ->assertSee('My Doubts')
            ->assertSee('My Topic Requests')
            ->assertSee('Logout');

        $dropdownStart = strpos($response->getContent(), '<div class="nav-user-dropdown-card">');
        $this->assertNotFalse($dropdownStart);

        $dropdown = substr($response->getContent(), $dropdownStart);
        $historyPosition = strpos($dropdown, 'Doubt Session History');
        $doubtsPosition = strpos($dropdown, 'My Doubts');
        $requestsPosition = strpos($dropdown, 'My Topic Requests');
        $logoutPosition = strpos($dropdown, 'Logout');

        $this->assertNotFalse($historyPosition);
        $this->assertNotFalse($doubtsPosition);
        $this->assertNotFalse($requestsPosition);
        $this->assertNotFalse($logoutPosition);
        $this->assertLessThan($doubtsPosition, $historyPosition);
        $this->assertLessThan($requestsPosition, $doubtsPosition);
        $this->assertLessThan($logoutPosition, $requestsPosition);

        $this->assertStringContainsString('href="' . route('doubts.mine') . '"', $dropdown);
        $this->assertStringContainsString('href="' . route('topic-requests.mine') . '"', $dropdown);
    }

    public function test_my_doubts_shows_only_the_authenticated_users_doubts_and_answers(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $answeredDoubt = Doubt::create([
            'user_id' => $user->id,
            'topic' => 'My submitted doubt',
            'message' => 'Please explain this topic.',
            'answer' => '<p>Here is the admin response.</p>',
            'status' => Doubt::STATUS_ANSWERED,
            'answered_by' => $otherUser->id,
            'answered_at' => now(),
        ]);
        Doubt::create([
            'user_id' => $user->id,
            'topic' => 'My pending doubt',
            'message' => 'I am waiting for a response.',
            'status' => Doubt::STATUS_PENDING,
        ]);
        Doubt::create([
            'user_id' => $otherUser->id,
            'topic' => 'Another users private doubt',
            'message' => 'This must not be visible.',
            'status' => Doubt::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->get(route('doubts.mine'))
            ->assertSee('My Doubts')
            ->assertOk()
            ->assertSee('My submitted doubt')
            ->assertSee('My pending doubt')
            ->assertSee('Here is the admin response.')
            ->assertSee('Answered')
            ->assertSee('Pending')
            ->assertDontSee('Another users private doubt')
            ->assertDontSee('This must not be visible.');

        $this->get(route('doubts.show', $answeredDoubt))
            ->assertOk()
            ->assertSee('Your question')
        ->assertSee('Back to My Doubts')
            ->assertSee('Here is the admin response.');
    }

    public function test_my_doubt_history_filters_pending_answered_and_closed_records(): void
    {
        $user = User::factory()->create();
        $doubts = [
            'pending' => Doubt::create([
                'user_id' => $user->id,
                'topic' => 'Pending topic',
                'message' => 'Waiting for help.',
                'status' => Doubt::STATUS_IN_PROGRESS,
            ]),
            'answered' => Doubt::create([
                'user_id' => $user->id,
                'topic' => 'Answered topic',
                'message' => 'Already answered.',
                'status' => Doubt::STATUS_ANSWERED,
            ]),
            'closed' => Doubt::create([
                'user_id' => $user->id,
                'topic' => 'Closed topic',
                'message' => 'This was closed.',
                'status' => Doubt::STATUS_REJECTED,
            ]),
        ];

        foreach (['pending', 'answered', 'closed'] as $status) {
            $this->actingAs($user)
                ->get(route('doubts.mine', ['status' => $status]))
                ->assertOk()
                ->assertSee($doubts[$status]->topic);

            foreach (array_diff(array_keys($doubts), [$status]) as $otherStatus) {
                $this->get(route('doubts.mine', ['status' => $status]))
                    ->assertDontSee($doubts[$otherStatus]->topic);
            }
        }

        $this->get(route('doubts.mine', ['search' => 'Waiting']))
            ->assertSee($doubts['pending']->topic)
            ->assertDontSee($doubts['answered']->topic);
    }

    public function test_my_doubts_search_and_subject_chips_use_database_subjects(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $anatomy = Subject::create([
            'name' => 'Human Anatomy',
            'slug' => 'human-anatomy-test',
            'status' => true,
        ]);
        $physiology = Subject::create([
            'name' => 'Physiology',
            'slug' => 'physiology-test',
            'status' => true,
        ]);
        $anatomyDoubt = Doubt::create([
            'user_id' => $user->id,
            'subject_id' => $anatomy->id,
            'topic' => 'Shoulder mobility',
            'message' => 'Why is this joint so mobile?',
            'answer' => '<p>It has a shallow socket.</p>',
            'status' => Doubt::STATUS_ANSWERED,
            'answered_at' => now(),
        ]);
        Doubt::create([
            'user_id' => $user->id,
            'subject_id' => $physiology->id,
            'topic' => 'Resting potential',
            'message' => 'Explain membrane potential.',
            'status' => Doubt::STATUS_PENDING,
        ]);
        Doubt::create([
            'user_id' => $otherUser->id,
            'subject_id' => $anatomy->id,
            'topic' => 'Private anatomy topic',
            'message' => 'This belongs to another user.',
            'status' => Doubt::STATUS_ANSWERED,
            'answered_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('doubts.mine'))
            ->assertOk()
            ->assertSee('Doubt & Answer Library')
            ->assertSee('Recently Answered Doubts')
            ->assertSee('Human Anatomy')
            ->assertSee('Physiology')
            ->assertSee('Shoulder mobility')
            ->assertSee('Read Full Answer')
            ->assertSee('<details', false)
            ->assertSee('It has a shallow socket.')
            ->assertDontSee('Private anatomy topic')
            ->assertDontSee('This belongs to another user.');

        $this->get(route('doubts.mine', ['subject_id' => $physiology->id]))
            ->assertOk()
            ->assertSee('Resting potential')
            ->assertDontSee('Shoulder mobility');

        $this->get(route('doubts.mine', ['search' => 'joint so mobile']))
            ->assertOk()
            ->assertSee('Shoulder mobility')
            ->assertDontSee('Resting potential');

        $this->assertSame(
            0,
            DoubtLike::where('doubt_id', $anatomyDoubt->id)->count()
        );
    }

    public function test_answered_doubt_can_be_liked_and_unliked_once_by_its_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $answeredDoubt = Doubt::create([
            'user_id' => $user->id,
            'topic' => 'Answered question',
            'message' => 'Question content.',
            'answer' => '<p>Answer content.</p>',
            'status' => Doubt::STATUS_ANSWERED,
            'answered_at' => now(),
        ]);
        $otherDoubt = Doubt::create([
            'user_id' => $otherUser->id,
            'topic' => 'Private answered question',
            'message' => 'Private content.',
            'answer' => '<p>Private answer.</p>',
            'status' => Doubt::STATUS_ANSWERED,
            'answered_at' => now(),
        ]);
        $pendingDoubt = Doubt::create([
            'user_id' => $user->id,
            'topic' => 'Pending question',
            'message' => 'Awaiting an answer.',
            'status' => Doubt::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->post(route('doubts.likes.store', $answeredDoubt))
            ->assertRedirect();
        $this->post(route('doubts.likes.store', $answeredDoubt))->assertRedirect();
        $this->assertDatabaseCount('doubt_likes', 1);
        $this->assertDatabaseHas('doubt_likes', [
            'doubt_id' => $answeredDoubt->id,
            'user_id' => $user->id,
        ]);

        $this->get(route('doubts.mine'))
            ->assertOk()
            ->assertSee('aria-pressed="true"', false)
            ->assertSee('doubt-like-count">1', false);

        $this->delete(route('doubts.likes.destroy', $answeredDoubt))->assertRedirect();
        $this->assertDatabaseCount('doubt_likes', 0);

        $this->post(route('doubts.likes.store', $otherDoubt))->assertNotFound();
        $this->post(route('doubts.likes.store', $pendingDoubt))->assertForbidden();
        $this->delete(route('doubts.likes.destroy', $otherDoubt))->assertNotFound();
        $this->assertDatabaseCount('doubt_likes', 0);
    }

    public function test_follow_up_is_kept_in_the_same_doubt_thread_and_reopens_it(): void
    {
        Notification::fake();
        Role::findOrCreate('admin', 'web');
        $user = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $doubt = Doubt::create([
            'user_id' => $user->id,
            'topic' => 'Follow-up topic',
            'message' => 'Original question.',
            'answer' => '<p>Initial admin answer.</p>',
            'answered_by' => $admin->id,
            'answered_at' => now()->subDay(),
            'status' => Doubt::STATUS_ANSWERED,
        ]);

        $this->actingAs($user)
            ->post(route('doubts.messages.store', $doubt), [
                'message' => 'Can you clarify this answer?',
            ])
            ->assertRedirect(route('doubts.show', $doubt));

        $doubt->refresh();
        $this->assertSame(Doubt::STATUS_PENDING, $doubt->status);
        $this->assertNull($doubt->answer);
        $this->assertDatabaseCount('doubts', 1);
        $this->assertDatabaseHas('doubt_messages', [
            'doubt_id' => $doubt->id,
            'sender_role' => 'admin',
            'message' => '<p>Initial admin answer.</p>',
        ]);
        $this->assertDatabaseHas('doubt_messages', [
            'doubt_id' => $doubt->id,
            'sender_id' => $user->id,
            'sender_role' => 'student',
            'message' => 'Can you clarify this answer?',
        ]);

        $adminHistory = $this->actingAs($admin, 'admin')
            ->get(route('admin.doubts.index'))
            ->assertOk()
            ->getContent();
        preg_match_all('/data-thread="([^"]*)"/', $adminHistory, $conversationAttributes);
        $decodedConversations = array_map('base64_decode', $conversationAttributes[1]);
        $this->assertTrue(collect($decodedConversations)->contains(
            fn ($conversation) => str_contains($conversation, 'Can you clarify this answer?')
        ));

        $this->actingAs($admin, 'admin')
            ->put(route('admin.doubts.update', $doubt), [
                'status' => Doubt::STATUS_ANSWERED,
                'answer' => '<p>Here is a clarification.</p>',
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->get(route('doubts.show', $doubt))
            ->assertOk()
            ->assertSee('Original question.')
            ->assertSee('Initial admin answer.')
            ->assertSee('Can you clarify this answer?')
            ->assertSee('Here is a clarification.');
        $this->assertDatabaseCount('doubts', 1);
        $this->assertDatabaseCount('doubt_messages', 3);
    }

    public function test_users_cannot_read_or_add_messages_to_another_users_doubt(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherDoubt = Doubt::create([
            'user_id' => $otherUser->id,
            'topic' => 'Private topic',
            'message' => 'Private question.',
            'status' => Doubt::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->get(route('doubts.show', $otherDoubt))
            ->assertNotFound();

        $this->post(route('doubts.messages.store', $otherDoubt), [
            'message' => 'Unauthorized follow-up.',
        ])->assertNotFound();

        $this->assertDatabaseCount('doubt_messages', 0);
    }

    public function test_my_topic_requests_shows_only_the_authenticated_users_requests_and_responses(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        TopicRequest::create([
            'user_id' => $user->id,
            'topic_name' => 'My requested topic',
            'description' => 'Please add this topic to the site.',
            'status' => TopicRequest::STATUS_RESOLVED,
            'admin_note' => 'This topic has been published.',
            'topic_link' => 'https://example.com/my-topic',
            'submission_token' => (string) str()->uuid(),
        ]);
        TopicRequest::create([
            'user_id' => $otherUser->id,
            'topic_name' => 'Another users private request',
            'description' => 'This request must not be visible.',
            'status' => TopicRequest::STATUS_PENDING,
            'submission_token' => (string) str()->uuid(),
        ]);

        $this->actingAs($user)
            ->get(route('topic-requests.mine'))
            ->assertOk()
            ->assertSee('My requested topic')
            ->assertSee('Please add this topic to the site.')
            ->assertSee('This topic has been published.')
            ->assertSee('Resolved')
            ->assertSee('https://example.com/my-topic')
            ->assertDontSee('Another users private request')
            ->assertDontSee('This request must not be visible.');
    }

    public function test_my_doubts_and_topic_requests_require_authentication(): void
    {
        $this->get(route('doubts.mine'))->assertRedirect(route('login'));
        $this->get(route('topic-requests.mine'))->assertRedirect(route('login'));
    }
}
