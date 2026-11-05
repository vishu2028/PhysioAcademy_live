<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FirebaseGoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_the_existing_firebase_google_sign_in_flow_without_server_secrets(): void
    {
        $response = $this->get(route('login'));
        $socialAuthScript = file_get_contents(public_path('ui-physio/social-auth.js'));

        $response
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee('Continue with Apple')
            ->assertSee('data-auth-consent', false)
            ->assertSee(route('support.terms'), false)
            ->assertSee(route('support.privacy-policy'), false)
            ->assertSee('social-auth.js')
            ->assertSee('window.physioFirebaseLoginUrl')
            ->assertSee(
                'window.physioFirebaseLoginUrl = '.json_encode(route('login.firebase', absolute: false)),
                false
            )
            ->assertSee(
                'challengeUrl: '.json_encode(route('login.apple.challenge', absolute: false)),
                false
            )
            ->assertDontSee('Forgot Password?')
            ->assertDontSee('name="email"')
            ->assertDontSee('name="password"')
            ->assertDontSee('private_key');

        $this->assertIsString($socialAuthScript);
        $this->assertStringContainsString('signInWithPopup', $socialAuthScript);
        $this->assertStringNotContainsString('signInWithRedirect', $socialAuthScript);
        $this->assertStringNotContainsString('getRedirectResult', $socialAuthScript);
        $this->assertStringContainsString('firebaseAuthReady', $socialAuthScript);
        $this->assertStringContainsString('credentials: \'same-origin\'', $socialAuthScript);
        $this->assertStringContainsString('credential.user.getIdToken()', $socialAuthScript);

        $this->assertStringContainsString(
            'https://apis.google.com',
            $response->headers->get('Content-Security-Policy')
        );
        $this->assertStringContainsString(
            'https://appleid.cdn-apple.com',
            $response->headers->get('Content-Security-Policy')
        );

        $contentSecurityPolicy = $response->headers->get('Content-Security-Policy');
        preg_match('/(?:^|;)\s*connect-src\s+([^;]+)/', $contentSecurityPolicy, $connectSrcMatch);
        preg_match('/(?:^|;)\s*script-src\s+([^;]+)/', $contentSecurityPolicy, $scriptSrcMatch);

        $this->assertNotEmpty($connectSrcMatch[1] ?? null);
        $this->assertNotEmpty($scriptSrcMatch[1] ?? null);
        $this->assertStringContainsString('https://www.gstatic.com', $connectSrcMatch[1]);
        $this->assertStringContainsString('https://www.gstatic.com', $scriptSrcMatch[1]);
        foreach ([
            'https://www.googleapis.com',
            'https://securetoken.googleapis.com',
            'https://identitytoolkit.googleapis.com',
            'https://*.firebaseapp.com',
            'https://appleid.apple.com',
            'https://*.razorpay.com',
            'https://cdn.ckeditor.com',
        ] as $existingOrigin) {
            $this->assertStringContainsString($existingOrigin, $connectSrcMatch[1]);
        }
        $this->assertSame(
            1,
            substr_count($contentSecurityPolicy, 'connect-src ')
        );
        $this->assertStringContainsString('https://apis.google.com', $scriptSrcMatch[1]);
        $this->assertStringContainsString('https://appleid.cdn-apple.com', $scriptSrcMatch[1]);
        $this->assertStringContainsString('https://checkout.razorpay.com', $scriptSrcMatch[1]);
        $this->assertCount(1, $response->headers->all('content-security-policy'));
    }

    public function test_site_wide_auth_modal_only_displays_google_and_apple_options(): void
    {
        Role::findOrCreate('user', 'web');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Continue with Google')
            ->assertSee('Continue with Apple')
            ->assertDontSee('auth-tabs')
            ->assertDontSee('name="password"')
            ->assertDontSee('Forgot Password?');
    }

    public function test_google_login_creates_a_verified_regular_user_and_keeps_the_session(): void
    {
        Role::findOrCreate('user', 'web');
        Role::findOrCreate('admin', 'web');
        $this->mockFirebaseUser();

        $response = $this->postJson(route('login.firebase'), [
            'id_token' => 'valid-firebase-id-token',
            'accepted_terms' => true,
        ]);

        $response->assertOk()->assertJson([
            'success' => true,
            'redirect' => route('home', absolute: false),
        ]);

        $user = User::where('email', 'google.user@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasRole('user'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame('firebase-google-uid', $user->firebase_uid);
        $this->assertDatabaseCount('users', 1);

        $this->get(route('profile.edit'))->assertOk();
        $this->assertAuthenticatedAs($user, 'web');
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_google_login_requires_terms_consent(): void
    {
        $this->postJson(route('login.firebase'), [
            'id_token' => 'valid-firebase-id-token',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['accepted_terms']);

        $this->assertGuest();
    }

    public function test_google_login_links_an_existing_account_by_email_without_creating_a_duplicate(): void
    {
        $existingUser = User::factory()->create([
            'name' => 'Existing Account',
            'email' => 'Google.User@example.com',
            'firebase_uid' => null,
            'email_verified_at' => null,
        ]);
        $this->mockFirebaseUser();

        $this->postJson(route('login.firebase'), [
            'id_token' => 'valid-firebase-id-token',
            'accepted_terms' => true,
        ])->assertOk();

        $this->assertAuthenticatedAs($existingUser);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('firebase-google-uid', $existingUser->fresh()->firebase_uid);
        $this->assertNotNull($existingUser->fresh()->email_verified_at);
    }

    public function test_google_login_uses_intended_page_but_never_redirects_a_regular_user_to_admin(): void
    {
        Role::findOrCreate('user', 'web');
        $this->mockFirebaseUser();

        $this->withSession(['url.intended' => '/topics'])
            ->postJson(route('login.firebase'), ['id_token' => 'valid-firebase-id-token', 'accepted_terms' => true])
            ->assertOk()
            ->assertJsonPath('redirect', '/topics');

        auth()->logout();
        $this->withSession(['url.intended' => '/admin/dashboard'])
            ->postJson(route('login.firebase'), ['id_token' => 'valid-firebase-id-token', 'accepted_terms' => true])
            ->assertOk()
            ->assertJsonPath('redirect', route('home', absolute: false));

        $this->assertAuthenticated();
        $this->assertFalse(auth()->user()->hasRole('admin'));
    }

    public function test_google_login_rejects_an_invalid_firebase_token_without_creating_a_user(): void
    {
        $this->mock(FirebaseService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('verifyToken')
                ->once()
                ->with('invalid-firebase-id-token')
                ->andThrow(new \RuntimeException('Invalid Firebase token.'));
        });

        $this->postJson(route('login.firebase'), [
            'id_token' => 'invalid-firebase-id-token',
            'accepted_terms' => true,
        ])->assertUnauthorized()->assertJson([
            'success' => false,
            'message' => 'Google sign-in could not verify your account. Please try again.',
        ]);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_google_login_cannot_authenticate_an_administrator_account(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create(['email' => 'google.user@example.com']);
        $admin->assignRole('admin');
        $this->mockFirebaseUser();

        $this->postJson(route('login.firebase'), [
            'id_token' => 'valid-firebase-id-token',
            'accepted_terms' => true,
        ])->assertForbidden();

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->assertNull($admin->fresh()->firebase_uid);
    }

    public function test_google_user_can_log_out_and_sign_in_again_without_creating_a_duplicate(): void
    {
        Role::findOrCreate('user', 'web');
        $this->mockFirebaseUser();

        $this->postJson(route('login.firebase'), [
            'id_token' => 'valid-firebase-id-token',
            'accepted_terms' => true,
        ])->assertOk();
        $user = User::where('firebase_uid', 'firebase-google-uid')->firstOrFail();
        $this->get(route('profile.edit'))->assertOk();

        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest('web');

        $this->postJson(route('login.firebase'), [
            'id_token' => 'valid-firebase-id-token',
            'accepted_terms' => true,
        ])->assertOk();

        $this->assertAuthenticatedAs($user, 'web');
        $this->get(route('profile.edit'))->assertOk();
        $this->assertDatabaseCount('users', 1);
    }

    private function mockFirebaseUser(): void
    {
        $this->mock(FirebaseService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('verifyToken')
                ->with('valid-firebase-id-token')
                ->andReturn((object) [
                    'uid' => 'firebase-google-uid',
                    'email' => 'google.user@example.com',
                    'emailVerified' => true,
                    'displayName' => 'Google User',
                    'photoUrl' => 'https://images.example.com/google-user.jpg',
                ]);
        });
    }
}
