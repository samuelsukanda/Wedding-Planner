<?php

namespace Tests\Feature;

use App\Http\Middleware\RedirectLegacyDomain;
use Illuminate\Http\Request;
use Tests\TestCase;

class DomainMigrationTest extends TestCase
{
    public function test_old_domain_redirects_to_new_domain_with_path_and_query(): void
    {
        $response = app(RedirectLegacyDomain::class)->handle(
            Request::create('https://weddingplanner.web.id/undangan/demo-token?utm_source=old'),
            fn () => response('next')
        );

        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('https://nupsera.my.id/undangan/demo-token?utm_source=old', $response->headers->get('Location'));
    }

    public function test_public_entry_has_nupsera_seo_and_structured_data(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://nupsera.my.id/">', false)
            ->assertSee('Nupsera', false)
            ->assertSee('WebApplication', false)
            ->assertSee('https://nupsera.my.id/llms.txt', false);
    }

    public function test_crawler_endpoints_are_public(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Sitemap: https://nupsera.my.id/sitemap.xml');
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml');
        $this->get('/llms.txt')->assertOk()->assertSee('Nupsera');
    }
}
