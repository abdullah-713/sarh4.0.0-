<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\EnsureAdminPanelAccess;
use App\Http\Middleware\SetPermissionsPolicy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class MiddlewareTest extends TestCase
{
    // ── EnsureAdminPanelAccess ──

    public function test_admin_access_denies_unauthenticated(): void
    {
        $middleware = new EnsureAdminPanelAccess();
        $request = Request::create('/admin');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $middleware->handle($request, fn() => new Response());
    }

    public function test_admin_access_allows_super_admin(): void
    {
        $user = new User();
        $user->is_super_admin = true;
        $user->security_level = 1;

        $request = Request::create('/admin');
        $request->setUserResolver(fn() => $user);

        $middleware = new EnsureAdminPanelAccess();
        $response = $middleware->handle($request, fn() => new Response('OK'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_admin_access_allows_level_4_plus(): void
    {
        $user = new User();
        $user->is_super_admin = false;
        $user->security_level = 4;

        $request = Request::create('/admin');
        $request->setUserResolver(fn() => $user);

        $middleware = new EnsureAdminPanelAccess();
        $response = $middleware->handle($request, fn() => new Response('OK'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_admin_access_denies_level_3(): void
    {
        $user = new User();
        $user->is_super_admin = false;
        $user->security_level = 3;

        $request = Request::create('/admin');
        $request->setUserResolver(fn() => $user);

        $middleware = new EnsureAdminPanelAccess();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $middleware->handle($request, fn() => new Response('OK'));
    }

    // ── SetPermissionsPolicy ──

    public function test_permissions_policy_sets_header(): void
    {
        $middleware = new SetPermissionsPolicy();
        $request = Request::create('/');

        $response = $middleware->handle($request, fn() => new Response('OK'));

        $this->assertTrue($response->headers->has('Permissions-Policy'));
        $policy = $response->headers->get('Permissions-Policy');
        $this->assertStringContainsString('geolocation=(self)', $policy);
        $this->assertStringContainsString('camera=()', $policy);
        $this->assertStringContainsString('microphone=()', $policy);
        $this->assertStringContainsString('payment=()', $policy);
    }

    public function test_permissions_policy_preserves_response_content(): void
    {
        $middleware = new SetPermissionsPolicy();
        $request = Request::create('/');

        $response = $middleware->handle($request, fn() => new Response('Test Content'));

        $this->assertEquals('Test Content', $response->getContent());
    }
}
