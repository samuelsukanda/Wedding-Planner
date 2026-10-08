<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** Root entry renders login for guests. */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertOk()->assertSee('Masuk');
    }

    public function test_legacy_login_url_redirects_to_root(): void
    {
        $this->get('/login')->assertRedirect(route('dashboard'));
    }
}
