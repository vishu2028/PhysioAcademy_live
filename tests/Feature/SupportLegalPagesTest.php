<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupportLegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_links_are_available_to_guests(): void
    {
        foreach ([
            'faq',
            'support.feedback',
            'support.help-center',
            'support.privacy-policy',
            'support.terms',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_admin_can_publish_and_update_a_support_page(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.support-legal-pages.index'))
            ->assertOk()
            ->assertSee('Support &amp; Legal Pages', false);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.support-legal-pages.update', 'privacy-policy'), [
                'title' => 'Privacy Policy',
                'content' => '<h2>Our promise</h2><p>Your privacy matters.</p>',
                'status' => '1',
            ])
            ->assertRedirect(route('admin.support-legal-pages.index'));

        $this->assertDatabaseHas('pages', [
            'slug' => 'privacy-policy',
            'status' => 1,
            'content' => '<h2>Our promise</h2><p>Your privacy matters.</p>',
        ]);

        $this->get(route('support.privacy-policy'))
            ->assertOk()
            ->assertSee('<h2>Our promise</h2><p>Your privacy matters.</p>', false);
    }

    public function test_unpublished_support_content_is_not_rendered(): void
    {
        Page::create([
            'title' => 'Private Terms',
            'slug' => 'terms-and-conditions',
            'content' => '<p>Private draft content.</p>',
            'status' => false,
        ]);

        $this->get(route('support.terms'))
            ->assertOk()
            ->assertDontSee('Private draft content.');
    }

    public function test_feedback_submission_uses_the_existing_message_workflow(): void
    {
        $this->from(route('support.feedback'))
            ->post(route('support.feedback.submit'), [
                'name' => 'Student',
                'email' => 'student@example.com',
                'message' => 'The new support page is helpful.',
            ])
            ->assertRedirect(route('support.feedback'));

        $this->assertDatabaseHas('messages', [
            'email' => 'student@example.com',
            'subject' => 'Website Feedback',
            'message' => 'The new support page is helpful.',
        ]);
    }
}
