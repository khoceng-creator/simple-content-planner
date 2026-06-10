<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_login_page(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('IMM Content Planner');
    }

    public function test_guest_cannot_access_brands_index(): void
    {
        $this->get(route('brands.index'))->assertRedirect(route('login'));
    }

    public function test_active_user_can_log_in_and_log_out(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret-pass')]);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'secret-pass'])
            ->assertRedirect(route('brands.index'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_is_logged_out_from_protected_pages(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)->get(route('brands.index'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong']);
        }

        $this->post(route('login'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
    }
}
