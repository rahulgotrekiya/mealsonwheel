<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Mail\MerchantApplicationReviewed;
use App\Models\User;
use App\Support\SalesReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MerchantOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Mail::fake();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@mealsonwheels.test')->firstOrFail();
    }

    private function applicant(): User
    {
        return User::where('email', 'newsupplier@mealsonwheels.test')->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    private function application(array $overrides = []): array
    {
        return array_merge([
            'firstname' => 'Barkwell',
            'lastname' => 'Foods',
            'email' => 'hello@barkwell.test',
            'phone' => '9820000111',
            'password' => 'correct-horse-battery',
        ], $overrides);
    }

    // Applying

    public function test_the_application_page_is_reachable_from_the_storefront(): void
    {
        $this->get('/')->assertSee(route('merchant.register'), false);

        $this->get('/become-a-supplier')
            ->assertOk()
            ->assertSee('Apply to supply', false);
    }

    public function test_applying_creates_an_account_that_is_waiting(): void
    {
        $this->post('/become-a-supplier', $this->application())
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $merchant = User::where('email', 'hello@barkwell.test')->firstOrFail();

        $this->assertSame(UserRole::Merchant, $merchant->role);
        $this->assertSame(UserStatus::Pending, $merchant->status);
    }

    public function test_applying_does_not_sign_the_applicant_in(): void
    {
        $this->post('/become-a-supplier', $this->application());

        // Approval is what grants access, so signing up must grant none.
        $this->assertGuest();
    }

    public function test_an_application_cannot_grant_itself_a_role_or_status(): void
    {
        $this->post('/become-a-supplier', $this->application([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]));

        $merchant = User::where('email', 'hello@barkwell.test')->firstOrFail();

        $this->assertSame(UserRole::Merchant, $merchant->role);
        $this->assertSame(UserStatus::Pending, $merchant->status);
    }

    public function test_an_application_rejects_a_duplicate_email(): void
    {
        $this->post('/become-a-supplier', $this->application(['email' => 'customer@mealsonwheels.test']))
            ->assertSessionHasErrors('email');
    }

    public function test_an_applicant_cannot_sign_in_or_reach_the_panel(): void
    {
        $applicant = $this->applicant();

        $response = $this->post('/login', ['email' => $applicant->email, 'password' => 'password']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString(
            'awaiting approval',
            $response->getSession()->get('errors')->first('email')
        );

        $this->actingAs($applicant)->get('/merchant')->assertRedirect('/login');
    }

    // The approval queue

    public function test_the_queue_needs_an_admin(): void
    {
        $this->get('/admin/merchants')->assertRedirect('/login');
    }

    public function test_a_merchant_cannot_open_the_queue(): void
    {
        $this->actingAs(User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail());

        $this->get('/admin/merchants')->assertForbidden();
    }

    public function test_the_queue_lists_waiting_applications(): void
    {
        $this->actingAs($this->admin())->get('/admin/merchants')
            ->assertOk()
            ->assertSee($this->applicant()->email, false)
            ->assertSee('1 waiting', false);
    }

    public function test_the_sidebar_shows_how_many_are_waiting(): void
    {
        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertSee('Merchants', false)
            ->assertSee('badge bg-danger ms-auto', false);
    }

    public function test_the_sidebar_badge_disappears_once_the_queue_is_empty(): void
    {
        $this->applicant()->update(['status' => UserStatus::Active]);

        $this->actingAs($this->admin())->get('/admin')
            ->assertOk()
            ->assertDontSee('badge bg-danger ms-auto', false);
    }

    public function test_a_merchant_never_sees_the_admin_menu(): void
    {
        $merchant = User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();

        $this->actingAs($merchant)->get('/merchant')
            ->assertOk()
            ->assertDontSee('>Merchants<', false)
            ->assertDontSee('>Categories<', false)
            ->assertDontSee('>Users<', false);
    }

    // Deciding

    public function test_approving_lets_the_merchant_in(): void
    {
        $applicant = $this->applicant();

        $this->actingAs($this->admin())
            ->patch("/admin/merchants/{$applicant->id}/approve")
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame(UserStatus::Active, $applicant->fresh()->status);

        Mail::assertSent(MerchantApplicationReviewed::class, fn ($mail) => $mail->approved === true);

        // The whole point: they can now sign in and reach their panel.
        $this->post('/login', ['email' => $applicant->email, 'password' => 'password'])
            ->assertRedirect('/merchant');

        $this->actingAs($applicant->fresh())->get('/merchant')->assertOk();
    }

    public function test_declining_keeps_them_out(): void
    {
        $applicant = $this->applicant();

        $this->actingAs($this->admin())
            ->patch("/admin/merchants/{$applicant->id}/reject")
            ->assertRedirect();

        $this->assertSame(UserStatus::Suspended, $applicant->fresh()->status);

        Mail::assertSent(MerchantApplicationReviewed::class, fn ($mail) => $mail->approved === false);

        $this->post('/login', ['email' => $applicant->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_a_declined_application_can_be_reinstated(): void
    {
        $applicant = $this->applicant();

        $this->actingAs($this->admin())->patch("/admin/merchants/{$applicant->id}/reject");
        $this->actingAs($this->admin())->patch("/admin/merchants/{$applicant->id}/approve");

        $this->assertSame(UserStatus::Active, $applicant->fresh()->status);
    }

    public function test_a_non_merchant_account_cannot_be_approved_through_this_screen(): void
    {
        $customer = User::where('email', 'customer@mealsonwheels.test')->firstOrFail();

        $this->actingAs($this->admin())
            ->patch("/admin/merchants/{$customer->id}/approve")
            ->assertSessionHasErrors('user');

        $this->assertTrue($customer->fresh()->isCustomer());
        Mail::assertNothingSent();
    }

    public function test_a_customer_cannot_approve_anyone(): void
    {
        $applicant = $this->applicant();

        $this->actingAs(User::factory()->create())
            ->patch("/admin/merchants/{$applicant->id}/approve")
            ->assertForbidden();

        $this->assertSame(UserStatus::Pending, $applicant->fresh()->status);
    }

    // The merchant's own dashboard

    public function test_the_dashboard_reports_only_this_merchants_figures(): void
    {
        $merchant = User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();

        $theirs = SalesReport::forSeller($merchant->id);
        $wholeStore = new SalesReport;

        $this->assertGreaterThan(0, $theirs->totalEarnings());
        $this->assertLessThan($wholeStore->totalEarnings(), $theirs->totalEarnings());

        $this->actingAs($merchant)->get('/merchant')
            ->assertOk()
            ->assertSee(number_format($theirs->netEarnings(), 2), false)
            ->assertDontSee(number_format($wholeStore->totalEarnings(), 2), false);
    }

    public function test_the_dashboard_flags_stock_running_low(): void
    {
        $merchant = User::where('email', 'pawsome@mealsonwheels.test')->firstOrFail();
        $product = $merchant->products()->firstOrFail();
        $product->update(['stock' => 2]);

        $this->actingAs($merchant)->get('/merchant')
            ->assertOk()
            ->assertSee($product->name, false)
            ->assertSee('2 left', false);
    }
}
