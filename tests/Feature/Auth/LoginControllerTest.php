<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_login_form(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function it_authenticates_user_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.orders.index'));
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_fails_authentication_with_invalid_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'Неверный email или пароль.']);
        $this->assertGuest();
    }

    /** @test */
    public function it_fails_authentication_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email' => 'Неверный email или пароль.']);
        $this->assertGuest();
    }

    /** @test */
    public function it_validates_required_email_field(): void
    {
        $response = $this->post(route('login'), [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_validates_email_format(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_validates_required_password_field(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function it_remembers_user_when_remember_is_checked(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->assertAuthenticatedAs($user);
        $response = $this->get('/');
        $response->assertCookie('remember_web_' . sha1(User::class));
    }

    /** @test */
    public function it_regenerates_session_on_successful_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $oldSessionId = session()->getId();

        $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $newSessionId = session()->getId();

        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /** @test */
    public function it_logs_out_user_successfully(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** @test */
    public function it_invalidates_session_on_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $sessionId = session()->getId();

        $this->post(route('logout'));

        $newSessionId = session()->getId();

        $this->assertNotEquals($sessionId, $newSessionId);
    }

    /** @test */
    public function it_redirects_to_intended_url_after_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Try to access protected page
        $this->get(route('admin.products.index'));

        // Login
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Should redirect to intended URL (admin.orders.index is default)
        $response->assertRedirect(route('admin.orders.index'));
    }
}
