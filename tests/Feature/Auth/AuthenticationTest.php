<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSee('Continue as User')
            ->assertSee('href="'.route('admin.login').'"', false)
            ->assertSee('Continue with Google')
            ->assertSee('Continue with Apple')
            ->assertDontSee('Forgot Password?')
            ->assertDontSee('name="email"')
            ->assertDontSee('name="password"');
    }

    public function test_user_password_login_is_not_available_on_the_user_login_route(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertMethodNotAllowed();
        $this->assertGuest();
    }

    public function test_admins_can_authenticate_using_the_separate_admin_login(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');
        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_routes_redirect_guests_to_the_separate_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_regular_users_cannot_use_the_admin_password_login(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Registered Admin Email')
            ->assertSee('Back to User Login')
            ->assertSee('href="'.route('login').'"', false);
        $this->post(route('admin.login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertGuest('admin');
    }

    public function test_admin_password_login_rejects_invalid_passwords(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_admin_and_user_logouts_only_end_their_own_guard_session(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $user = User::factory()->create();

        $this->actingAs($user, 'web');
        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertAuthenticatedAs($admin, 'admin');

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertGuest('admin');

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));
        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest('web');
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_regular_user_cannot_access_admin_routes_even_when_signed_in(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->assertAuthenticatedAs($user, 'web');
        $this->assertGuest('admin');
    }

    public function test_super_admin_can_authenticate_using_the_admin_guard(): void
    {
        Role::findOrCreate('super_admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard', absolute: false));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->get(route('admin.dashboard'))->assertOk();
    }
}
