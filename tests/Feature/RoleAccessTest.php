<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Each panel is reachable only by its own role, and the check runs against the
 * database on every request rather than against whatever the session recorded
 * at sign-in.
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public static function panels(): array
    {
        return [
            'admin panel' => ['/admin'],
            'merchant panel' => ['/merchant'],
        ];
    }

    #[DataProvider('panels')]
    public function test_a_guest_is_sent_to_sign_in(string $panel): void
    {
        $this->get($panel)->assertRedirect('/login');
    }

    #[DataProvider('panels')]
    public function test_a_customer_is_forbidden(string $panel): void
    {
        $this->actingAs(User::factory()->create())->get($panel)->assertForbidden();
    }

    public function test_an_admin_reaches_only_the_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/merchant')->assertForbidden();
    }

    public function test_a_merchant_reaches_only_the_merchant_panel(): void
    {
        $merchant = User::factory()->merchant()->create();

        $this->actingAs($merchant)->get('/merchant')->assertOk();
        $this->actingAs($merchant)->get('/admin')->assertForbidden();
    }

    public function test_an_unapproved_merchant_is_turned_away(): void
    {
        $merchant = User::factory()->pending()->create();

        $this->actingAs($merchant)->get('/merchant')->assertRedirect('/login');
    }

    public function test_suspending_a_user_takes_effect_on_their_next_request(): void
    {
        $merchant = User::factory()->merchant()->create();

        $this->actingAs($merchant)->get('/merchant')->assertOk();

        // Suspended mid-session: the very next request must be rejected, without
        // waiting for the session to lapse.
        $merchant->update(['status' => UserStatus::Suspended]);

        $this->actingAs($merchant)->get('/merchant')->assertRedirect('/login');
    }

    public function test_demoting_a_user_takes_effect_on_their_next_request(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin')->assertOk();

        $admin->update(['role' => UserRole::Customer]);

        $this->actingAs($admin)->get('/admin')->assertForbidden();
    }

    public function test_a_rejected_session_is_cleared_rather_than_left_open(): void
    {
        $merchant = User::factory()->merchant()->create();
        $merchant->update(['status' => UserStatus::Suspended]);

        $this->actingAs($merchant)->get('/merchant')->assertRedirect('/login');

        $this->assertGuest();
    }
}
