<?php

namespace Tests\Feature;

use App\Models\Akun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_active_account_can_login_with_username(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('panel.dashboard'));
        $this->assertAuthenticatedAs(Akun::where('username', 'superadmin')->first());
        $this->get('/panel')->assertOk()->assertJson(['authenticated' => true]);
    }

    public function test_inactive_account_is_rejected_and_active_middleware_logs_out_account(): void
    {
        $this->seed();
        $account = Akun::where('username', 'superadmin')->firstOrFail();

        $account->update(['status_aktif' => false]);
        $this->post('/login', ['username' => 'superadmin', 'password' => 'password'])
            ->assertSessionHasErrors('username');

        $account->update(['status_aktif' => true]);
        $this->actingAs($account);
        $account->update(['status_aktif' => false]);
        $this->get('/panel')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_five_attempts_per_username_and_ip(): void
    {
        $this->seed();

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', ['username' => 'superadmin', 'password' => 'wrong-password'])
                ->assertSessionHasErrors('username');
        }

        $this->post('/login', ['username' => 'superadmin', 'password' => 'wrong-password'])
            ->assertTooManyRequests();
    }
}
