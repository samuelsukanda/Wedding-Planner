<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Smoke test untuk halaman publik. Dashboard berada di dalam middleware
     * auth, jadi request ke / akan mengarahkan ke halaman login.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}