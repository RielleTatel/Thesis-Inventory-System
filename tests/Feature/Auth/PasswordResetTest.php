<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function test_forgot_password_shows_admin_mediated_instructions(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
        $response->assertSee('CITS');
        $response->assertSee('Back to sign in');
    }

    public function test_email_based_reset_routes_are_removed(): void
    {
        // The self-service "email me a reset link" + token flow is gone — no
        // dead endpoint remains for a system with no email infrastructure.
        $this->assertFalse(Route::has('password.email'));
        $this->assertFalse(Route::has('password.reset'));
        $this->assertFalse(Route::has('password.store'));

        // forgot-password is now a GET-only instructions page — no email handler.
        $this->post('/forgot-password', ['email' => 'someone@univ.edu'])->assertStatus(405);
        // The token reset-password path is gone entirely.
        $this->post('/reset-password', [])->assertNotFound();

        // The instructions page the login link points to still exists.
        $this->assertTrue(Route::has('password.request'));
    }
}
