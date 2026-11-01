<?php

namespace Tests\Feature;

use App\Models\Doubt;
use App\Models\TopicRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        Doubt::create([
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
            ->assertOk()
            ->assertSee('My submitted doubt')
            ->assertSee('My pending doubt')
            ->assertSee('Here is the admin response.')
            ->assertSee('Answered')
            ->assertSee('Pending')
            ->assertDontSee('Another users private doubt')
            ->assertDontSee('This must not be visible.');
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
