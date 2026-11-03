<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaTest extends TestCase
{
    public function test_app_manifest_service_worker_and_offline_page_are_served(): void
    {
        $this->withoutVite();

        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true);
        $this->assertSame('standalone', $manifest['display']);
        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }

        $this->assertFileExists(public_path('sw.js'));
        $this->get(route('offline'))->assertOk()->assertSee("You're offline", false)->assertDontSee('Log in');
        $this->get(route('login'))->assertSee('manifest.webmanifest', false);
    }
}
