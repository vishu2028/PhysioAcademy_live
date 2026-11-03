<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NewsletterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_subscribe_and_duplicate_emails_are_reported_without_duplicate_records(): void
    {
        $this->travelTo(Carbon::parse('2026-10-31 14:25:36'));

        $this->postJson(route('newsletter.subscribe'), ['email' => ' Reader@Example.com '])
            ->assertOk()
            ->assertJson([
                'message' => "Thank you for subscribing! You'll receive updates from PhysioSource.",
                'subscribed' => true,
                'already_subscribed' => false,
            ]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'reader@example.com',
            'status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
            'subscribed_at' => '2026-10-31 14:25:36',
        ]);

        $this->postJson(route('newsletter.subscribe'), ['email' => 'READER@example.com'])
            ->assertOk()
            ->assertJson([
                'message' => 'You are already subscribed!',
                'subscribed' => true,
                'already_subscribed' => true,
            ]);

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_invalid_email_is_rejected_with_validation_errors(): void
    {
        $this->postJson(route('newsletter.subscribe'), ['email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_unsubscribed_email_is_reactivated_and_subscription_time_is_refreshed(): void
    {
        $subscriber = NewsletterSubscriber::create([
            'email' => 'reader@example.com',
            'subscribed_at' => '2026-01-01 08:00:00',
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
        ]);
        $this->travelTo(Carbon::parse('2026-10-31 14:25:36'));

        $this->postJson(route('newsletter.subscribe'), ['email' => 'reader@example.com'])
            ->assertOk()
            ->assertJson([
                'message' => "Thank you for subscribing! You'll receive updates from PhysioSource.",
                'subscribed' => true,
                'already_subscribed' => false,
            ]);

        $this->assertDatabaseCount('newsletter_subscribers', 1);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $subscriber->id,
            'email' => 'reader@example.com',
            'status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
            'subscribed_at' => '2026-10-31 14:25:36',
        ]);
    }

    public function test_admin_can_search_filter_view_and_delete_subscribers(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $matchingSubscriber = NewsletterSubscriber::create([
            'email' => 'reader@example.com',
            'subscribed_at' => '2026-10-31 14:25:36',
            'status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
        ]);
        NewsletterSubscriber::create([
            'email' => 'former@example.com',
            'subscribed_at' => '2026-10-30 09:00:00',
            'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.newsletter-subscribers.index', [
                'search' => 'reader',
                'status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
            ]))
            ->assertOk()
            ->assertSee('Newsletter Subscribers')
            ->assertSee('reader@example.com')
            ->assertSee('Oct 31, 2026')
            ->assertSee($matchingSubscriber->subscribed_at->format('h:i:s A'))
            ->assertDontSee('former@example.com');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.newsletter-subscribers.index', [
                'status' => NewsletterSubscriber::STATUS_UNSUBSCRIBED,
            ]))
            ->assertOk()
            ->assertSee('former@example.com')
            ->assertDontSee('reader@example.com');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.newsletter-subscribers.show', [
                'newsletter_subscriber' => $matchingSubscriber,
                'search' => 'reader',
                'status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
            ]))
            ->assertOk()
            ->assertSee('reader@example.com')
            ->assertSee('Subscribed')
            ->assertSee('search=reader');

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.newsletter-subscribers.destroy', [
                'newsletter_subscriber' => $matchingSubscriber,
                'search' => 'reader',
                'status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
            ]))
            ->assertRedirect(route('admin.newsletter-subscribers.index', [
                'search' => 'reader',
                'status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
            ]));

        $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $matchingSubscriber->id]);
    }
}
