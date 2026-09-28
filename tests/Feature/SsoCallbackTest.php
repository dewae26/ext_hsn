<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SsoCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(array $overrides = []): User
    {
        return User::create(array_merge([
            'employee_id' => '61260004',
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'is_super_admin' => true,
            'is_active' => true,
        ], $overrides));
    }

    public function test_redirect_to_sso_login_url(): void
    {
        config(['services.sso.login_url' => 'https://sso.hasnurgroup.com/sso/login/app_test']);

        $this->get(route('sso.redirect'))
            ->assertRedirect('https://sso.hasnurgroup.com/sso/login/app_test');
    }

    public function test_whitelisted_nrp_can_login_via_sso(): void
    {
        $user = $this->admin();

        Http::fake([
            '*/api/sso/verify-token' => Http::response([
                'valid' => true,
                'user' => ['nrp' => '61260004', 'name' => 'Admin Test', 'email' => 'admin@example.com'],
            ]),
        ]);

        $this->post(route('sso.callback'), [
            'session_id' => 'sess-1',
            'sso_token' => 'token-1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_nrp_not_in_whitelist_is_rejected(): void
    {
        Http::fake([
            '*/api/sso/verify-token' => Http::response([
                'valid' => true,
                'user' => ['nrp' => '99999999', 'name' => 'Orang Lain', 'email' => 'lain@example.com'],
            ]),
        ]);

        $this->post(route('sso.callback'), [
            'session_id' => 'sess-2',
            'sso_token' => 'token-2',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_invalid_token_is_rejected(): void
    {
        Http::fake([
            '*/api/sso/verify-token' => Http::response(['valid' => false], 200),
        ]);

        $this->post(route('sso.callback'), [
            'session_id' => 'sess-3',
            'sso_token' => 'token-3',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_inactive_admin_is_rejected(): void
    {
        $this->admin(['is_active' => false]);

        Http::fake([
            '*/api/sso/verify-token' => Http::response([
                'valid' => true,
                'user' => ['nrp' => '61260004', 'name' => 'Admin Test', 'email' => 'admin@example.com'],
            ]),
        ]);

        $this->post(route('sso.callback'), [
            'session_id' => 'sess-4',
            'sso_token' => 'token-4',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
