<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminHelpTest extends TestCase
{
    use RefreshDatabase;

    public function test_help_support_ticket_page_links_to_the_existing_rootflash_module(): void
    {
        $admin = $this->createAdmin();
        config(['services.rootflash.admin_url' => 'https://rootflash.example.test']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.help.support-ticket'))
            ->assertOk()
            ->assertSee('RootFlash Support Tickets')
            ->assertSee('https://rootflash.example.test/admin/tickets', false);
    }

    public function test_help_dropdown_contains_exactly_two_options_and_is_expanded_on_help_pages(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.help.subscription'));

        $response->assertOk()
            ->assertSee('Subscription')
            ->assertSee('no recurring subscription plans')
            ->assertSee('webinar, group webinar, and doubt-session bookings')
            ->assertSee('aria-expanded="true"', false);

        $html = $response->getContent();
        $dropdownPosition = strpos($html, 'id="adminHelpDropdown"');
        $openingTagEnd = strpos($html, '>', $dropdownPosition);
        $closingTagStart = strpos($html, '</div>', $openingTagEnd);
        $this->assertNotFalse($dropdownPosition);
        $this->assertNotFalse($openingTagEnd);
        $this->assertNotFalse($closingTagStart);
        $dropdown = substr($html, $openingTagEnd + 1, $closingTagStart - $openingTagEnd - 1);
        preg_match_all('/<a\\b/', $dropdown, $links);
        $this->assertCount(2, $links[0]);
        $this->assertStringContainsString('Support Ticket', $dropdown);
        $this->assertStringContainsString('Subscription', $dropdown);
    }

    public function test_unconfigured_rootflash_admin_url_is_not_rendered_as_a_link(): void
    {
        $admin = $this->createAdmin();
        config(['services.rootflash.admin_url' => 'javascript:alert(1)']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.help.support-ticket'))
            ->assertOk()
            ->assertSee('ROOTFLASH_ADMIN_URL')
            ->assertDontSee('javascript:alert(1)', false);
    }

    private function createAdmin(): User
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }
}
