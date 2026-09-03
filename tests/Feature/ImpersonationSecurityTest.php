<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Http\Middleware\EnforceMfa;
use App\Http\Middleware\EnforceReadOnlyImpersonation;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ImpersonationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_only_mfa_confirmed_super_admin_can_impersonate_users(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        $professor = $this->createUser('Professor');
        $student = $this->createUser('Aluno');
        $userWithoutRole = User::factory()->create();

        $this->actingAs($admin);

        $this->assertFalse($admin->canImpersonate());

        session()->put(EnforceMfa::SESSION_KEY, $admin->getKey());

        $this->assertTrue($admin->canImpersonate());
        $this->assertTrue($professor->canBeImpersonated());
        $this->assertTrue($student->canBeImpersonated());
        $this->assertFalse($userWithoutRole->canBeImpersonated());
    }

    public function test_valid_impersonator_mfa_allows_reading_as_professor(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        $professor = $this->createMfaUser('Professor', now()->subDay());

        $this->actingAs($professor)
            ->withSession($this->impersonationSession($admin))
            ->get('/maestro')
            ->assertOk();

        $this->assertAuthenticatedAs($professor);
    }

    public function test_filament_action_starts_professor_impersonation(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        $professor = $this->createUser('Professor');

        $this->actingAs($admin);
        session()->put(EnforceMfa::SESSION_KEY, $admin->getKey());

        Livewire::test(ListUsers::class)
            ->assertTableActionVisible('impersonate', $professor)
            ->call('mountTableAction', 'impersonate', (string) $professor->getKey());

        $this->assertAuthenticatedAs($professor);
        $this->assertSame($admin->getKey(), session(config('laravel-impersonate.session_key')));
        $this->assertSame($admin->getKey(), session(EnforceMfa::SESSION_KEY));

        $this->get('/maestro')
            ->assertOk()
            ->assertSee('modo de leitura');

        $this->get('/maestro/configurar-autenticacao-multifator')->assertForbidden();
        $this->assertFalse($professor->twoFactorAuth()->exists());

        $this->get('/filament-impersonate/leave')->assertStatus(405);
        $this->assertAuthenticatedAs($professor);

        $this->post('/filament-impersonate/leave')->assertRedirect('/maestro');

        $this->assertAuthenticatedAs($admin);
        $this->assertSame($admin->getKey(), session(EnforceMfa::SESSION_KEY));
        $this->get('/maestro')->assertOk();
    }

    public function test_impersonation_with_invalid_mfa_session_is_terminated(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        $professor = $this->createUser('Professor');
        $session = $this->impersonationSession($admin);
        $session[EnforceMfa::SESSION_KEY] = $professor->getKey();

        $this->actingAs($professor)
            ->withSession($session)
            ->get('/maestro')
            ->assertRedirect('/maestro/login');

        $this->assertGuest();
    }

    public function test_impersonation_of_user_without_panel_access_returns_to_admin(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        $userWithoutRole = User::factory()->create();

        $this->actingAs($userWithoutRole)
            ->withSession($this->impersonationSession($admin))
            ->get('/maestro')
            ->assertRedirect('/maestro');

        $this->assertAuthenticatedAs($admin);
        $this->assertFalse(session()->has(config('laravel-impersonate.session_key')));
    }

    public function test_read_only_middleware_blocks_writes_and_mutating_livewire_calls(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        session()->put($this->impersonationSession($admin));

        $middleware = app(EnforceReadOnlyImpersonation::class);

        $this->expectException(HttpException::class);

        $middleware->handle(
            $this->makeRequest('POST', 'test/write', 'test.write'),
            fn () => response('written'),
        );
    }

    public function test_read_only_middleware_allows_safe_requests_and_read_only_livewire_calls(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        session()->put($this->impersonationSession($admin));

        $middleware = app(EnforceReadOnlyImpersonation::class);

        $safeResponse = $middleware->handle(
            $this->makeRequest('GET', 'test/read', 'test.read'),
            fn () => response('read'),
        );

        $livewireResponse = $middleware->handle(
            $this->makeRequest('POST', 'livewire/update', 'default.livewire.update', [
                'components' => [[
                    'calls' => [['method' => 'gotoPage']],
                ]],
            ]),
            fn () => response('paginated'),
        );

        $this->assertSame('read', $safeResponse->getContent());
        $this->assertSame('paginated', $livewireResponse->getContent());
    }

    public function test_mutating_livewire_action_is_blocked_during_impersonation(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        session()->put($this->impersonationSession($admin));

        $this->expectException(HttpException::class);

        app(EnforceReadOnlyImpersonation::class)->handle(
            $this->makeRequest('POST', 'livewire/update', 'default.livewire.update', [
                'components' => [[
                    'calls' => [['method' => 'callMountedTableAction']],
                ]],
            ]),
            fn () => response('mutated'),
        );
    }

    public function test_form_interaction_is_allowed_during_impersonation(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        session()->put($this->impersonationSession($admin));

        $response = app(EnforceReadOnlyImpersonation::class)->handle(
            $this->makeRequest('POST', 'livewire/update', 'default.livewire.update', [
                'components' => [[
                    'calls' => [['method' => 'updatedInteractsWithForms']],
                    'updates' => ['data.id_subject' => 41],
                ]],
            ]),
            fn () => response('form-updated'),
        );

        $this->assertSame('form-updated', $response->getContent());
    }

    public function test_form_select_queries_are_allowed_during_impersonation(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        session()->put($this->impersonationSession($admin));

        $response = app(EnforceReadOnlyImpersonation::class)->handle(
            $this->makeRequest('POST', 'livewire/update', 'default.livewire.update', [
                'components' => [[
                    'calls' => [['method' => 'getFormSelectOptions']],
                ]],
            ]),
            fn () => response('options'),
        );

        $this->assertSame('options', $response->getContent());
    }

    public function test_email_verification_route_is_blocked_during_impersonation(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        session()->put($this->impersonationSession($admin));

        $this->expectException(HttpException::class);

        app(EnforceReadOnlyImpersonation::class)->handle(
            $this->makeRequest('GET', 'verify-email/1/hash', 'verification.verify'),
            fn () => response('verified'),
        );
    }

    public function test_crud_gate_abilities_are_denied_during_impersonation(): void
    {
        $admin = $this->createMfaUser('Super Admin');
        $professor = $this->createUser('Professor');

        $this->actingAs($professor);
        session()->put($this->impersonationSession($admin));

        $this->assertFalse(Gate::allows('update', $professor));
        $this->assertFalse(Gate::allows('delete', $professor));
    }

    /** @return array<string, int|string> */
    private function impersonationSession(User $admin): array
    {
        return [
            config('laravel-impersonate.session_key') => $admin->getKey(),
            config('laravel-impersonate.session_guard') => 'web',
            config('laravel-impersonate.session_guard_using') => 'web',
            'impersonate.guard' => 'web',
            'impersonate.back_to' => '/maestro',
            EnforceMfa::SESSION_KEY => $admin->getKey(),
        ];
    }

    /** @param array<string, mixed> $input */
    private function makeRequest(string $method, string $uri, string $routeName, array $input = []): Request
    {
        $request = Request::create($uri, $method, $input);
        $request->setLaravelSession(app('session')->driver());

        $route = new Route([$method], $uri, fn () => null);
        $route->name($routeName);
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    private function createUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findByName($role));

        return $user;
    }

    private function createMfaUser(string $role, mixed $graceUntil = null): User
    {
        $user = User::factory()->create([
            'mfa_grace_until' => $graceUntil ?? now()->addDays(7),
        ]);
        $user->assignRole(Role::findByName($role));
        $user->createTwoFactorAuth();
        $user->enableTwoFactorAuth();

        return $user->fresh();
    }
}
