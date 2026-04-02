<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    /**
     * Verify that SecurityHeadersMiddleware attaches the required headers
     * to every response, including unauthenticated 401 responses.
     */
    public function test_security_headers_present_on_api_response(): void
    {
        // /api/user/status requires auth, so it returns 401 without touching the DB.
        // The SecurityHeadersMiddleware still runs and must attach all headers.
        $response = $this->getJson('/api/user/status');

        $response->assertStatus(401);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('Permissions-Policy');
    }

    public function test_x_powered_by_header_not_exposed(): void
    {
        $response = $this->getJson('/api/user/status');
        $response->assertHeaderMissing('X-Powered-By');
    }
}
