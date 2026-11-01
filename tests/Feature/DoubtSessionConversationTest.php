<?php

namespace Tests\Feature;

use App\Jobs\SendNotificationEmail;
use App\Models\DoubtSessionBooking;
use App\Models\DoubtSessionFollowUp;
use App\Models\User;
use App\Notifications\DoubtSessionFollowUpAnswered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

class DoubtSessionConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_like_unlike_and_repeated_likes_do_not_duplicate(): void
    {
        $student = User::factory()->create();
        $booking = $this->bookingFor($student, ['admin_notes' => 'Helpful response']);
        $likeUrl = route('doubt-sessions.likes.store', $booking);

        $this->actingAs($student)
            ->from(route('doubt-sessions.history'))
            ->post($likeUrl)
            ->assertRedirect(route('doubt-sessions.history'));

        $this->post($likeUrl)->assertRedirect();
        $this->assertDatabaseCount('doubt_session_likes', 1);
        $this->assertDatabaseHas('doubt_session_likes', [
            'doubt_session_booking_id' => $booking->id,
            'user_id' => $student->id,
        ]);
        $this->get(route('doubt-sessions.history'))
            ->assertOk()
            ->assertSee('Unlike')
            ->assertSee('session-like-count">1', false);

        $this->delete(route('doubt-sessions.likes.destroy', $booking))
            ->assertRedirect();

        $this->assertDatabaseCount('doubt_session_likes', 0);
    }

    public function test_student_cannot_like_a_session_without_an_admin_response(): void
    {
        $student = User::factory()->create();
        $booking = $this->bookingFor($student);

        $this->actingAs($student)
            ->post(route('doubt-sessions.likes.store', $booking))
            ->assertForbidden();

        $this->assertDatabaseCount('doubt_session_likes', 0);
    }

    public function test_student_can_submit_multiple_follow_ups_for_their_booking(): void
    {
        $student = User::factory()->create();
        $booking = $this->bookingFor($student);
        $url = route('doubt-sessions.follow-ups.store', $booking);

        $this->actingAs($student)
            ->post($url, ['question' => 'Could you explain this further?'])
            ->assertRedirect();
        $this->post($url, ['question' => 'I have another follow-up question.'])
            ->assertRedirect();

        $this->assertDatabaseCount('doubt_session_follow_ups', 2);
        $this->assertDatabaseHas('doubt_session_follow_ups', [
            'doubt_session_booking_id' => $booking->id,
            'question' => 'Could you explain this further?',
            'status' => DoubtSessionFollowUp::STATUS_PENDING,
        ]);
    }

    public function test_students_cannot_like_or_submit_follow_ups_for_another_users_booking(): void
    {
        $student = User::factory()->create();
        $booking = $this->bookingFor(User::factory()->create(), [
            'admin_notes' => 'Private admin response',
        ]);

        $this->actingAs($student)
            ->post(route('doubt-sessions.likes.store', $booking))
            ->assertNotFound();

        $this->post(route('doubt-sessions.follow-ups.store', $booking), [
            'question' => 'Unauthorized question',
        ])->assertNotFound();

        $this->assertDatabaseCount('doubt_session_likes', 0);
        $this->assertDatabaseCount('doubt_session_follow_ups', 0);
    }

    public function test_student_can_only_see_their_session_conversation(): void
    {
        $student = User::factory()->create();
        $otherStudent = User::factory()->create();
        $ownBooking = $this->bookingFor($student, ['admin_notes' => 'My response']);
        $otherBooking = $this->bookingFor($otherStudent, ['admin_notes' => 'Private response']);
        $ownBooking->followUps()->create([
            'question' => 'My question',
        ]);
        $otherBooking->followUps()->create([
            'question' => 'Another student question',
        ]);

        $this->actingAs($student)
            ->get(route('doubt-sessions.history'))
            ->assertOk()
            ->assertSee('My response')
            ->assertSee('My question')
            ->assertDontSee('Private response')
            ->assertDontSee('Another student question');

        $this->assertDatabaseHas('doubt_session_follow_ups', [
            'doubt_session_booking_id' => $ownBooking->id,
        ]);
    }

    public function test_admin_can_answer_follow_up_and_student_receives_database_and_email_notifications(): void
    {
        Queue::fake();
        SpatieRole::findOrCreate('admin', 'web');
        $student = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $booking = $this->bookingFor($student);
        $followUp = $booking->followUps()->create([
            'question' => 'Can you clarify this step?',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.doubt-session-bookings.follow-ups.update', [$booking, $followUp]), [
                'answer' => 'Here is a more detailed explanation.',
            ])
            ->assertRedirect(route('admin.doubt-session-bookings.show', $booking));

        $followUp->refresh();
        $this->assertSame(DoubtSessionFollowUp::STATUS_ANSWERED, $followUp->status);
        $this->assertSame('Here is a more detailed explanation.', $followUp->answer);
        $this->assertSame($admin->id, $followUp->answered_by);
        $this->assertNotNull($followUp->answered_at);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $student->id,
            'type' => DoubtSessionFollowUpAnswered::class,
        ]);
        Queue::assertPushed(SendNotificationEmail::class);

        $this->get(route('admin.doubt-session-bookings.show', $booking))
            ->assertOk()
            ->assertSee('Can you clarify this step?')
            ->assertSee('Here is a more detailed explanation.')
            ->assertSee('Answered');

        $this->actingAs($student)
            ->get(route('doubt-sessions.history'))
            ->assertOk()
            ->assertSee('Here is a more detailed explanation.');
    }

    public function test_admin_cannot_reply_to_a_follow_up_through_a_different_booking(): void
    {
        SpatieRole::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $student = User::factory()->create();
        $booking = $this->bookingFor($student);
        $otherBooking = $this->bookingFor($student);
        $followUp = $booking->followUps()->create([
            'question' => 'Session-specific question',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.doubt-session-bookings.follow-ups.update', [$otherBooking, $followUp]), [
                'answer' => 'Wrong booking response.',
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('doubt_session_follow_ups', [
            'id' => $followUp->id,
            'answer' => null,
            'status' => DoubtSessionFollowUp::STATUS_PENDING,
        ]);
    }

    private function bookingFor(User $student, array $attributes = []): DoubtSessionBooking
    {
        return DoubtSessionBooking::create(array_merge([
            'booking_reference' => 'DS-' . Str::upper(Str::random(12)),
            'user_id' => $student->id,
            'student_name' => $student->name,
            'student_email' => $student->email,
            'phone' => '1234567890',
            'topic' => 'Anatomy',
            'doubt' => 'A question about the session topic.',
            'duration_minutes' => 60,
            'amount' => 0,
            'currency' => 'INR',
            'is_free' => true,
            'payment_status' => DoubtSessionBooking::PAYMENT_NOT_REQUIRED,
            'booking_status' => DoubtSessionBooking::STATUS_PENDING_SCHEDULE,
        ], $attributes));
    }
}
