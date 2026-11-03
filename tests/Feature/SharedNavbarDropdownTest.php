<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SharedNavbarDropdownTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_header_dropdowns_are_closed_until_explicitly_clicked(): void
    {
        Role::findOrCreate('user', 'web');
        $user = User::factory()->create();

        foreach ([route('home'), route('doubts.mine')] as $url) {
            $response = $this->actingAs($user)->get($url)->assertOk();
            $html = $response->getContent();

            $headEnd = strpos($html, '</head>');
            $bodyStart = strpos($html, '<body');

            $this->assertNotFalse($headEnd);
            $this->assertNotFalse($bodyStart);
            $this->assertLessThan($bodyStart, $headEnd);
            $this->assertStringContainsString(
                '[data-notification-menu] > [data-notification-dropdown]',
                substr($html, 0, $headEnd)
            );
            $this->assertStringContainsString(
                '.nav-user-menu > .nav-user-dropdown',
                substr($html, 0, $headEnd)
            );
            $this->assertStringContainsString('flex: 0 0 54px;', $html);
            $this->assertStringContainsString('flex-basis: 46px;', $html);
            $this->assertStringContainsString('object-fit: cover;', $html);
            $this->assertStringContainsString('object-position: center;', $html);
            $this->assertStringContainsString(
                'transition: box-shadow 0.22s ease, border-color 0.22s ease;',
                $html
            );
            $this->assertMatchesRegularExpression(
                '/\\.nav-user-avatar:hover\\s*\\{[^}]*box-shadow:[^}]*\\}/s',
                $html
            );
            preg_match('/\\.nav-user-avatar:hover\\s*\\{([^}]*)\\}/s', $html, $avatarHoverStyles);
            $this->assertArrayHasKey(1, $avatarHoverStyles);
            $this->assertStringNotContainsString('transform:', $avatarHoverStyles[1]);
            $this->assertStringNotContainsString('animation:', $avatarHoverStyles[1]);

            $response
                ->assertSee('aria-label="Open notifications"', false)
                ->assertSee('aria-controls="student-notification-dropdown"', false)
                ->assertSee('aria-expanded="false"', false)
                ->assertSee('id="student-notification-dropdown"', false)
                ->assertSee('aria-hidden="true"', false)
                ->assertSee('aria-label="Open profile menu"', false)
                ->assertSee('id="nav-user-dropdown"', false)
                ->assertSee('const openMenuSelector', false)
                ->assertSee('event.key !== \'Escape\'', false);
        }

        $this->assertStringNotContainsString(
            '.nav-user-menu:hover .nav-user-dropdown',
            $html
        );
        $this->assertStringNotContainsString(
            '.nav-user-menu:focus-within .nav-user-dropdown',
            $html
        );
    }
}
