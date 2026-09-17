<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_sign_in_page_renders(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sign in');
    }

    public function test_a_customer_can_sign_in_and_lands_on_the_storefront(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_an_admin_lands_on_the_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin');
    }

    public function test_a_merchant_lands_on_the_merchant_panel(): void
    {
        $merchant = User::factory()->merchant()->create();

        $this->post('/login', ['email' => $merchant->email, 'password' => 'password'])
            ->assertRedirect('/merchant');
    }

    public function test_a_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_the_error_does_not_reveal_whether_an_account_exists(): void
    {
        $user = User::factory()->create();

        $unknown = $this->post('/login', ['email' => 'nobody@example.test', 'password' => 'secret123'])
            ->assertSessionHasErrors('email');

        $wrongPassword = $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertSame(
            $unknown->getSession()->get('errors')->first('email'),
            $wrongPassword->getSession()->get('errors')->first('email'),
            'the two failures produce different messages, which enumerates accounts'
        );
    }

    public function test_a_pending_merchant_cannot_sign_in_even_with_the_right_password(): void
    {
        $merchant = User::factory()->pending()->create();

        $response = $this->post('/login', [
            'email' => $merchant->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString(
            'awaiting approval',
            $response->getSession()->get('errors')->first('email')
        );
    }

    public function test_a_suspended_user_cannot_sign_in(): void
    {
        $user = User::factory()->suspended()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_sign_in_attempts_are_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password']);
        }

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString(
            'Too many sign-in attempts',
            $response->getSession()->get('errors')->first('email')
        );

        RateLimiter::clear(User::class.'|'.mb_strtolower($user->email).'|127.0.0.1');
    }

    public function test_the_session_id_changes_on_sign_in(): void
    {
        $user = User::factory()->create();

        $this->startSession();
        $before = session()->getId();

        $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $this->assertNotSame($before, session()->getId(), 'session was not regenerated on sign-in');
    }

    public function test_a_visitor_can_register_as_a_customer(): void
    {
        $this->post('/register', [
            'firstname' => 'Meera',
            'lastname' => 'Iyer',
            'email' => 'meera@example.test',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])->assertRedirect('/');

        $user = User::where('email', 'meera@example.test')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_cannot_grant_a_role(): void
    {
        // A crafted field must not be able to mass-assign privilege.
        $this->post('/register', [
            'firstname' => 'Mallory',
            'lastname' => 'Quinn',
            'email' => 'mallory@example.test',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertSame(
            UserRole::Customer,
            User::where('email', 'mallory@example.test')->first()->role
        );
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        $existing = User::factory()->create();

        $this->post('/register', [
            'firstname' => 'Copy',
            'lastname' => 'Cat',
            'email' => $existing->email,
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])->assertSessionHasErrors('email');
    }

    public function test_a_signed_in_user_visiting_login_goes_to_their_own_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/login')->assertRedirect('/admin');
    }

    public function test_a_user_can_sign_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }
}
