<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Doubt;
use App\Models\DoubtMessage;
use App\Models\DoubtSessionBooking;
use App\Models\DoubtSessionFollowUp;
use App\Models\Subject;
use App\Models\User;
use App\Services\DoubtDiscussionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CommunityDoubtHubTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_and_admin_share_public_doubt_threads_and_only_followers_are_notified(): void
    {
        Role::findOrCreate('admin', 'web');
        $studentA = User::factory()->create([
            'name' => 'Student Alpha',
            'avatar' => 'https://images.example.test/student-alpha.png',
        ]);
        $studentB = User::factory()->create(['name' => 'Student Beta']);
        $studentC = User::factory()->create(['name' => 'Student Gamma']);
        $admin = User::factory()->create(['name' => 'Physio Admin']);
        $admin->assignRole('admin');
        $year = AcademicYear::create([
            'name' => 'Year 1',
            'slug' => 'community-year-1',
            'status' => true,
        ]);
        $subject = Subject::create([
            'name' => 'Community Anatomy',
            'slug' => 'community-anatomy',
            'status' => true,
        ]);

        $this->from(route('doubts.mine'))
            ->actingAs($studentA)
            ->post(route('doubts.store'), [
                'academic_year_id' => $year->id,
                'subject_id' => $subject->id,
                'topic' => 'Shoulder mobility',
                'message' => 'Why can this joint move in so many directions?',
            ])
            ->assertRedirect(route('doubts.mine'));

        $doubt = Doubt::query()->where('user_id', $studentA->id)->firstOrFail();
        $this->assertDatabaseHas('discussion_follows', [
            'user_id' => $studentA->id,
            'followable_type' => Doubt::class,
            'followable_id' => $doubt->id,
        ]);

        $this->actingAs($studentB)
            ->get(route('doubts.mine'))
            ->assertOk()
            ->assertSee('Shoulder mobility')
            ->assertSee('Why can this joint move in so many directions?')
            ->assertSee('Student Alpha')
            ->assertSee('https://images.example.test/student-alpha.png')
            ->assertSee('Student')
            ->assertSee('Add follow-up')
            ->assertDontSee('community-follow-up-question')
            ->assertDontSee('Start a follow-up');

        $this->from(route('doubts.show', $doubt))
            ->actingAs($studentB)
            ->post(route('doubts.follow.store', $doubt))
            ->assertRedirect();
        $this->post(route('doubts.follow.store', $doubt))->assertRedirect();
        $this->assertSame(1, $doubt->discussionFollows()->where('user_id', $studentB->id)->count());

        $this->post(route('doubts.messages.store', $doubt), [
            'message' => 'I have a similar question about the socket.',
        ])->assertRedirect(route('doubts.show', $doubt));
        $studentReply = DoubtMessage::query()
            ->where('doubt_id', $doubt->id)
            ->where('sender_id', $studentB->id)
            ->firstOrFail();

        foreach ([$studentA, $studentC] as $reader) {
            $this->actingAs($reader)
                ->get(route('doubts.show', $doubt))
                ->assertOk()
                ->assertSee('Student Alpha')
                ->assertSee('Original doubt')
                ->assertSee('Student Beta')
                ->assertSee('I have a similar question about the socket.');
        }

        $this->actingAs($admin, 'admin')
            ->put(route('admin.doubts.update', $doubt), [
                'status' => Doubt::STATUS_ANSWERED,
                'answer' => '<p>The shallow socket allows a wider range of movement.</p>',
            ])
            ->assertRedirect();

        $adminReply = DoubtMessage::query()
            ->where('doubt_id', $doubt->id)
            ->where('sender_role', DoubtMessage::ROLE_ADMIN)
            ->firstOrFail();
        $this->assertSame($admin->id, $adminReply->sender_id);

        foreach ([$studentA, $studentB, $studentC] as $reader) {
            $this->actingAs($reader)
                ->get(route('doubts.show', $doubt))
                ->assertOk()
                ->assertSee('Physio Admin')
                ->assertSee('Admin')
                ->assertSee('The shallow socket allows a wider range of movement.');
        }

        $this->assertSame(1, $studentA->notifications()->where('data->notification_key', 'doubt_discussion_reply_'.$studentReply->id)->count());
        $this->assertSame(1, $studentB->notifications()->where('data->notification_key', 'doubt_discussion_reply_'.$adminReply->id)->count());
        $this->assertSame(0, $studentC->notifications()->where('data->type', 'doubt_discussion_reply')->count());
        $this->assertSame(1, $studentA->notifications()->where('data->type', 'doubt_answered')->where('data->doubt_id', $doubt->id)->count());
        $this->assertSame(0, $studentA->notifications()->where('data->notification_key', 'doubt_discussion_reply_'.$adminReply->id)->count());
        app(DoubtDiscussionService::class)->notifyFollowers($doubt, $adminReply, [$studentA->id]);
        $this->assertSame(1, $studentB->notifications()->where('data->notification_key', 'doubt_discussion_reply_'.$adminReply->id)->count());
        $adminReplyNotification = $studentB->notifications()
            ->where('data->notification_key', 'doubt_discussion_reply_'.$adminReply->id)
            ->firstOrFail();
        $this->actingAs($studentB)
            ->post(route('notifications.open', $adminReplyNotification->id))
            ->assertRedirect(route('doubts.show', $doubt).'#doubt-message-'.$adminReply->id);

        $this->actingAs($studentB)
            ->delete(route('doubts.follow.destroy', $doubt))
            ->assertRedirect();
        $this->assertDatabaseMissing('discussion_follows', [
            'user_id' => $studentB->id,
            'followable_type' => Doubt::class,
            'followable_id' => $doubt->id,
        ]);
    }

    public function test_public_legacy_threads_are_in_my_doubts_but_booking_follow_ups_remain_private(): void
    {
        $author = User::factory()->create(['name' => 'Legacy Thread Author']);
        $reader = User::factory()->create(['name' => 'Community Reader']);
        $booking = DoubtSessionBooking::create([
            'booking_reference' => 'DS-'.Str::upper(Str::random(12)),
            'user_id' => $author->id,
            'student_name' => $author->name,
            'student_email' => $author->email,
            'phone' => '9876543210',
            'topic' => 'Private booking topic',
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
        $booking->followUps()->create([
            'user_id' => $author->id,
            'thread_id' => 9001,
            'author_role' => DoubtSessionFollowUp::ROLE_USER,
            'question' => 'Confidential one-on-one session follow-up.',
        ]);

        $thread = DoubtSessionFollowUp::create([
            'user_id' => $author->id,
            'doubt_session_booking_id' => null,
            'thread_id' => null,
            'author_role' => DoubtSessionFollowUp::ROLE_USER,
            'submission_key' => (string) Str::uuid(),
            'question' => 'An older public community question.',
            'status' => DoubtSessionFollowUp::STATUS_PENDING,
        ]);
        $thread->update(['thread_id' => $thread->id]);
        $reply = DoubtSessionFollowUp::create([
            'user_id' => $reader->id,
            'doubt_session_booking_id' => null,
            'parent_id' => $thread->id,
            'thread_id' => $thread->id,
            'author_role' => DoubtSessionFollowUp::ROLE_USER,
            'submission_key' => (string) Str::uuid(),
            'question' => 'An older reply that must remain visible.',
            'status' => DoubtSessionFollowUp::STATUS_PENDING,
        ]);

        $this->actingAs($reader)
            ->get(route('doubts.mine'))
            ->assertOk()
            ->assertSee('An older public community question.')
            ->assertSee('An older reply that must remain visible.')
            ->assertSee('Legacy Thread Author')
            ->assertSee('Community Reader')
            ->assertDontSee('Confidential one-on-one session follow-up.');

        $this->get(route('follow-ups.index'))
            ->assertRedirect(route('doubts.mine'));
        $this->get(route('follow-ups.show', $thread))
            ->assertRedirect(route('doubts.mine', ['discussion_id' => $thread->id]).'#community-thread-'.$thread->id);
        $this->get(route('doubts.mine', ['discussion_id' => $thread->id]))
            ->assertOk()
            ->assertSee('An older public community question.')
            ->assertSee('An older reply that must remain visible.');

        $this->assertDatabaseHas('doubt_session_follow_ups', [
            'id' => $thread->id,
            'question' => 'An older public community question.',
        ]);
        $this->assertDatabaseHas('doubt_session_follow_ups', [
            'id' => $reply->id,
            'question' => 'An older reply that must remain visible.',
        ]);
        $this->assertDatabaseCount('doubt_session_follow_ups', 3);
    }
}
