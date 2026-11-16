<?php

namespace Tests\Feature;

use App\Models\DoubtSessionBooking;
use App\Models\DoubtSessionFollowUp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FollowUpDiscussionTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_and_admin_can_create_a_persistent_nested_discussion(): void
    {
        Queue::fake();
        Role::findOrCreate('admin', 'web');
        $author = User::factory()->create(['name' => 'Author User']);
        $participant = User::factory()->create(['name' => 'Participant User']);
        $thirdUser = User::factory()->create(['name' => 'Third User']);
        $admin = User::factory()->create(['name' => 'Admin User']);
        $admin->assignRole('admin');

        $rootKey = (string) Str::uuid();
        $this->actingAs($author)->post(route('follow-ups.store'), [
            'question' => 'How should I approach this exercise?',
            'submission_key' => $rootKey,
        ])->assertRedirect();

        $thread = DoubtSessionFollowUp::where('submission_key', $rootKey)->firstOrFail();
        $this->post(route('follow-ups.store'), [
            'question' => 'Duplicate submission',
            'submission_key' => $rootKey,
        ])->assertRedirect();
        $this->assertDatabaseCount('doubt_session_follow_ups', 1);

        $participantKey = (string) Str::uuid();
        $this->actingAs($participant)->post(route('follow-ups.replies.store', $thread), [
            'question' => 'Start gently and focus on form.',
            'parent_id' => $thread->id,
            'submission_key' => $participantKey,
        ])->assertRedirect();
        $participantReply = DoubtSessionFollowUp::where('submission_key', $participantKey)->firstOrFail();
        $this->post(route('follow-ups.replies.store', $thread), [
            'question' => 'Duplicate reply',
            'parent_id' => $thread->id,
            'submission_key' => $participantKey,
        ])->assertRedirect();
        $this->assertDatabaseCount('doubt_session_follow_ups', 2);

        $authorKey = (string) Str::uuid();
        $this->actingAs($author)->post(route('follow-ups.replies.store', $thread), [
            'question' => 'Thanks, that helps.',
            'parent_id' => $participantReply->id,
            'submission_key' => $authorKey,
        ])->assertRedirect();

        $thirdKey = (string) Str::uuid();
        $this->actingAs($thirdUser)->post(route('follow-ups.replies.store', $thread), [
            'question' => 'I found this useful too.',
            'parent_id' => $thread->id,
            'submission_key' => $thirdKey,
        ])->assertRedirect();
        $thirdReply = DoubtSessionFollowUp::where('submission_key', $thirdKey)->firstOrFail();

        $adminKey = (string) Str::uuid();
        $this->actingAs($admin, 'admin')
            ->get(route('admin.follow-ups.show', $thread))
            ->assertOk()
            ->assertSee('Author User')
            ->assertSee('Participant User');
        $this->post(route('admin.follow-ups.replies.store', $thread), [
            'question' => 'Please consult a qualified instructor if you feel pain.',
            'parent_id' => $thirdReply->id,
            'submission_key' => $adminKey,
        ])->assertRedirect(route('admin.follow-ups.show', $thread));

        $adminReply = DoubtSessionFollowUp::where('submission_key', $adminKey)->firstOrFail();
        $this->assertSame($thread->id, $adminReply->thread_id);
        $this->assertSame(DoubtSessionFollowUp::ROLE_ADMIN, $adminReply->author_role);
        $this->assertSame($admin->id, $adminReply->user_id);
        $this->assertNotNull($adminReply->created_at);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $author->id,
            'data->type' => 'follow_up_discussion_reply',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $participant->id,
            'data->type' => 'follow_up_discussion_reply',
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $thirdUser->id,
            'data->type' => 'follow_up_discussion_reply',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $admin->id,
            'data->type' => 'follow_up_discussion_reply',
        ]);

        $this->actingAs($author)
            ->get(route('follow-ups.show', $thread))
            ->assertOk()
            ->assertSee('Author User')
            ->assertSee('Participant User')
            ->assertSee('Third User')
            ->assertSee('Admin User')
            ->assertSee('Admin')
            ->assertSee('Please consult a qualified instructor if you feel pain.');

        $this->post(route('logout'))->assertRedirect('/');
        $this->actingAs($author)
            ->get(route('follow-ups.show', $thread))
            ->assertOk()
            ->assertSee('How should I approach this exercise?')
            ->assertSee('Thanks, that helps.');

        $this->assertDatabaseCount('doubt_session_follow_ups', 5);
    }

    public function test_admin_can_hide_and_restore_content_without_deleting_it(): void
    {
        Role::findOrCreate('admin', 'web');
        $author = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $thread = $this->createThread($author);

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.follow-ups.visibility.update', $thread))
            ->assertRedirect();

        $this->assertDatabaseHas('doubt_session_follow_ups', [
            'id' => $thread->id,
            'is_hidden' => true,
        ]);
        $this->actingAs($author)->get(route('follow-ups.show', $thread))->assertNotFound();

        $this->actingAs($admin, 'admin')
            ->patch(route('admin.follow-ups.visibility.update', $thread))
            ->assertRedirect();

        $this->assertDatabaseHas('doubt_session_follow_ups', [
            'id' => $thread->id,
            'is_hidden' => false,
        ]);
    }

    public function test_follow_up_moderation_and_admin_routes_require_admin_authorization(): void
    {
        $author = User::factory()->create();
        $thread = $this->createThread($author);

        $this->actingAs($author)
            ->get(route('admin.follow-ups.index'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($author)
            ->patch(route('admin.follow-ups.visibility.update', $thread))
            ->assertRedirect(route('admin.login'));

        $this->assertDatabaseHas('doubt_session_follow_ups', [
            'id' => $thread->id,
            'is_hidden' => false,
        ]);
    }

    public function test_community_feed_does_not_expose_existing_private_booking_follow_ups(): void
    {
        $owner = User::factory()->create();
        $booking = $this->bookingFor($owner);
        $booking->followUps()->create([
            'user_id' => $owner->id,
            'thread_id' => 999,
            'author_role' => DoubtSessionFollowUp::ROLE_USER,
            'question' => 'Private medical session follow-up',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('follow-ups.index'))
            ->assertOk()
            ->assertDontSee('Private medical session follow-up');
    }

    public function test_only_the_booking_owner_and_admin_can_participate_in_private_follow_ups(): void
    {
        Role::findOrCreate('admin', 'web');
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $booking = $this->bookingFor($owner);
        $thread = $booking->followUps()->create([
            'user_id' => $owner->id,
            'thread_id' => 1,
            'author_role' => DoubtSessionFollowUp::ROLE_USER,
            'question' => 'Private booking follow-up',
        ]);
        $thread->update(['thread_id' => $thread->id]);

        $this->actingAs($otherUser)
            ->post(route('doubt-sessions.follow-ups.replies.store', [$booking, $thread]), [
                'question' => 'Unauthorized private reply',
                'parent_id' => $thread->id,
                'submission_key' => (string) Str::uuid(),
            ])->assertNotFound();

        $this->actingAs($owner)
            ->post(route('doubt-sessions.follow-ups.replies.store', [$booking, $thread]), [
                'question' => 'Owner reply',
                'parent_id' => $thread->id,
                'submission_key' => (string) Str::uuid(),
            ])->assertRedirect();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.follow-ups.replies.store', $thread), [
                'question' => 'Admin private response',
                'parent_id' => $thread->id,
                'submission_key' => (string) Str::uuid(),
            ])->assertRedirect();

        $this->assertDatabaseCount('doubt_session_follow_ups', 3);
        $ownerNotification = $owner->notifications()
            ->where('data->type', 'follow_up_discussion_reply')
            ->firstOrFail();
        $this->assertStringNotContainsString('Admin private response', $ownerNotification->data['message']);
        $this->assertStringNotContainsString('Private booking follow-up', $ownerNotification->data['message']);
    }

    public function test_reply_must_belong_to_the_requested_thread(): void
    {
        $author = User::factory()->create();
        $firstThread = $this->createThread($author);
        $otherThread = $this->createThread($author);

        $this->actingAs(User::factory()->create())
            ->post(route('follow-ups.replies.store', $firstThread), [
                'question' => 'Cross-thread reply',
                'parent_id' => $otherThread->id,
                'submission_key' => (string) Str::uuid(),
            ])->assertNotFound();

        $this->assertDatabaseCount('doubt_session_follow_ups', 2);
    }

    private function createThread(User $author): DoubtSessionFollowUp
    {
        $key = (string) Str::uuid();
        $this->actingAs($author)->post(route('follow-ups.store'), [
            'question' => 'A persistent community discussion',
            'submission_key' => $key,
        ])->assertRedirect();

        return DoubtSessionFollowUp::where('submission_key', $key)->firstOrFail();
    }

    private function bookingFor(User $student): DoubtSessionBooking
    {
        return DoubtSessionBooking::create([
            'booking_reference' => 'DS-'.Str::upper(Str::random(12)),
            'user_id' => $student->id,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'phone' => '9876543210',
            'topic' => 'Private session',
            'preferred_slots' => [['date' => now()->addDay()->toDateString(), 'time' => '10:00']],
            'duration_minutes' => 30,
            'amount' => 0,
            'original_amount' => 0,
            'discount_amount' => 0,
            'currency' => 'INR',
            'is_free' => true,
            'payment_status' => DoubtSessionBooking::PAYMENT_NOT_REQUIRED,
            'booking_status' => DoubtSessionBooking::STATUS_PENDING_SCHEDULE,
        ]);
    }
}
