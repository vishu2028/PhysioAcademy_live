<?php

namespace Tests\Feature;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppleLoginTest extends TestCase
{
    use RefreshDatabase;

    private string $privateKey;

    private array $jwks;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.apple.client_id', 'in.physiosource.web');
        config()->set('services.apple.redirect_uri', 'https://physiosource.in/login/apple/callback');

        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        $this->assertNotFalse($key);
        $privateKey = '';
        $this->assertTrue(openssl_pkey_export($key, $privateKey));
        $this->privateKey = $privateKey;

        $details = openssl_pkey_get_details($key);
        $this->jwks = [
            'keys' => [[
                'kty' => 'RSA',
                'use' => 'sig',
                'alg' => 'RS256',
                'kid' => 'apple-test-key',
                'n' => $this->base64UrlEncode($details['rsa']['n']),
                'e' => $this->base64UrlEncode($details['rsa']['e']),
            ]],
        ];
    }

    public function test_new_apple_identity_registers_a_verified_regular_user(): void
    {
        Role::findOrCreate('user', 'web');
        [$challenge, $token] = $this->challengeAndToken([
            'sub' => 'apple-sub-100',
            'email' => 'private-relay@privaterelay.appleid.com',
            'email_verified' => 'true',
        ]);

        $this->postJson(route('login.apple'), [
            'id_token' => $token,
            'state' => $challenge['state'],
            'name' => 'Apple Student',
            'accepted_terms' => true,
        ])->assertOk()->assertJson([
            'success' => true,
            'redirect' => route('home', absolute: false),
        ]);

        $user = User::where('apple_sub', 'apple-sub-100')->firstOrFail();
        $this->assertSame('private-relay@privaterelay.appleid.com', $user->email);
        $this->assertSame('Apple Student', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('user'));
        $this->assertFalse($user->hasAnyRole(['admin', 'super_admin']));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
        $this->get(route('profile.edit'))->assertOk();
    }

    public function test_existing_account_is_linked_by_verified_email_without_a_duplicate(): void
    {
        Role::findOrCreate('user', 'web');
        $existing = User::factory()->create([
            'email' => 'student@example.com',
            'apple_sub' => null,
        ]);
        $existing->assignRole('user');
        [$challenge, $token] = $this->challengeAndToken([
            'sub' => 'apple-sub-existing',
            'email' => 'STUDENT@example.com',
            'email_verified' => true,
        ]);

        $this->postJson(route('login.apple'), [
            'id_token' => $token,
            'state' => $challenge['state'],
            'accepted_terms' => true,
        ])->assertOk();

        $this->assertAuthenticatedAs($existing);
        $this->assertSame('apple-sub-existing', $existing->fresh()->apple_sub);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_returning_private_relay_account_can_sign_in_without_email_claim(): void
    {
        Role::findOrCreate('user', 'web');
        $existing = User::factory()->create([
            'apple_sub' => 'apple-sub-relay',
            'email' => 'relay@privaterelay.appleid.com',
        ]);
        $existing->assignRole('user');
        [$challenge, $token] = $this->challengeAndToken([
            'sub' => 'apple-sub-relay',
        ]);

        $this->postJson(route('login.apple'), [
            'id_token' => $token,
            'state' => $challenge['state'],
            'accepted_terms' => true,
        ])->assertOk();

        $this->assertAuthenticatedAs($existing);
        $this->assertSame('relay@privaterelay.appleid.com', $existing->fresh()->email);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_apple_login_cannot_authenticate_an_administrator_account(): void
    {
        Role::findOrCreate('admin', 'web');
        $admin = User::factory()->create([
            'email' => 'student@example.com',
            'apple_sub' => null,
        ]);
        $admin->assignRole('admin');
        [$challenge, $token] = $this->challengeAndToken([
            'sub' => 'apple-sub-admin',
            'email' => 'student@example.com',
            'email_verified' => true,
        ]);

        $this->postJson(route('login.apple'), [
            'id_token' => $token,
            'state' => $challenge['state'],
            'accepted_terms' => true,
        ])->assertForbidden();

        $this->assertGuest();
        $this->assertNull($admin->fresh()->apple_sub);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_apple_login_rejects_invalid_nonce_and_replayed_state(): void
    {
        [$challenge, $token] = $this->challengeAndToken([
            'sub' => 'apple-sub-invalid-nonce',
            'email' => 'student@example.com',
            'email_verified' => true,
            'nonce' => 'incorrect',
        ]);

        $this->postJson(route('login.apple'), [
            'id_token' => $token,
            'state' => $challenge['state'],
            'accepted_terms' => true,
        ])->assertUnauthorized();

        $this->postJson(route('login.apple'), [
            'id_token' => $token,
            'state' => $challenge['state'],
            'accepted_terms' => true,
        ])->assertStatus(419);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_apple_user_can_log_out_and_sign_in_again_without_a_duplicate(): void
    {
        Role::findOrCreate('user', 'web');
        [$challenge, $token] = $this->challengeAndToken([
            'sub' => 'apple-sub-repeat',
            'email' => 'repeat@example.com',
            'email_verified' => true,
        ]);

        $this->postJson(route('login.apple'), [
            'id_token' => $token,
            'state' => $challenge['state'],
            'accepted_terms' => true,
        ])->assertOk();
        $user = User::where('apple_sub', 'apple-sub-repeat')->firstOrFail();

        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();

        [$challenge, $token] = $this->challengeAndToken([
            'sub' => 'apple-sub-repeat',
            'email' => 'repeat@example.com',
            'email_verified' => true,
        ]);
        $this->postJson(route('login.apple'), [
            'id_token' => $token,
            'state' => $challenge['state'],
            'accepted_terms' => true,
        ])->assertOk();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_apple_challenge_reports_missing_developer_configuration(): void
    {
        config()->set('services.apple.client_id', null);
        config()->set('services.apple.redirect_uri', null);

        $this->postJson(route('login.apple.challenge'), ['accepted_terms' => true])
            ->assertServiceUnavailable()
            ->assertJson([
                'success' => false,
                'message' => 'Apple sign-in is not configured for this website yet.',
            ]);
    }

    public function test_apple_challenge_requires_terms_consent(): void
    {
        $this->postJson(route('login.apple.challenge'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['accepted_terms']);
    }

    private function challengeAndToken(array $claims): array
    {
        Cache::forget('apple.identity_jwks');
        Http::fake([
            'https://appleid.apple.com/auth/keys' => Http::response($this->jwks),
        ]);

        $challenge = $this->postJson(route('login.apple.challenge'), ['accepted_terms' => true])
            ->assertOk()
            ->json();

        $payload = array_merge([
            'iss' => 'https://appleid.apple.com',
            'aud' => 'in.physiosource.web',
            'iat' => now()->timestamp,
            'exp' => now()->addMinutes(5)->timestamp,
            'nonce' => $challenge['nonce'],
        ], $claims);

        $token = JWT::encode($payload, $this->privateKey, 'RS256', 'apple-test-key');

        return [$challenge, $token];
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
