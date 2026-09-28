<?php

namespace Tests\Feature\Hospital;

use App\Models\Hospital;
use App\Models\HospitalLoginLog;
use App\Models\VerificationLog;
use App\Services\EmployeeLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginAndLookupTest extends TestCase
{
    use RefreshDatabase;

    protected function hospital(array $overrides = []): Hospital
    {
        $hospital = Hospital::create(array_merge([
            'name' => 'RS Contoh',
            'slug' => 'rs-contoh',
            'pic_phone' => '081234567890',
            'is_active' => true,
        ], $overrides));

        $hospital->contacts()->create([
            'label' => 'PIC',
            'phone' => $hospital->pic_phone,
            'phone_normalized' => Hospital::normalizePhone($hospital->pic_phone),
            'is_active' => true,
        ]);

        return $hospital;
    }

    public function test_login_works_with_any_registered_contact(): void
    {
        config(['hasnurverif.otp.enabled' => false]);
        $hospital = $this->hospital();
        $hospital->contacts()->create([
            'label' => 'Depok',
            'phone' => '081999999999',
            'phone_normalized' => '6281999999999',
            'is_active' => true,
        ]);

        $this->post(route('hospital.otp.request', $hospital->slug), ['phone' => '081999999999'])
            ->assertRedirect(route('hospital.dashboard', $hospital->slug));

        $this->assertAuthenticatedAs($hospital, 'hospital');
    }

    public function test_registered_phone_can_login_when_otp_disabled(): void
    {
        config(['hasnurverif.otp.enabled' => false]);
        $hospital = $this->hospital();

        $this->post(route('hospital.otp.request', $hospital->slug), ['phone' => '081234567890'])
            ->assertRedirect(route('hospital.dashboard', $hospital->slug));

        $this->assertAuthenticatedAs($hospital, 'hospital');
        $this->assertDatabaseHas('hospital_login_logs', [
            'hospital_id' => $hospital->id,
            'status' => 'success',
            'hospital_name' => 'RS Contoh',
            'pic_label' => 'PIC',
        ]);
    }

    public function test_unregistered_phone_is_rejected(): void
    {
        config(['hasnurverif.otp.enabled' => false]);
        $hospital = $this->hospital();

        $this->post(route('hospital.otp.request', $hospital->slug), ['phone' => '0800000000'])
            ->assertSessionHas('error');

        $this->assertGuest('hospital');
        $this->assertDatabaseHas('hospital_login_logs', [
            'hospital_id' => $hospital->id,
            'status' => 'failed',
            'reason' => 'nomor_tidak_terdaftar',
        ]);
    }

    public function test_active_employee_lookup_returns_active_and_logs(): void
    {
        $hospital = $this->hospital();
        $this->actingAs($hospital, 'hospital');

        $this->mock(EmployeeLookupService::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn([
                'result' => 'active',
                'feedback' => 'Karyawan Aktif',
                'nrp' => '61260004',
                'employee_name' => 'Eriton Latti Dewa',
                'group_company' => 'SBU Mining',
                'company_name' => 'PT. Graha Nusa Minergi',
            ]);
        });

        $this->post(route('hospital.lookup', $hospital->slug), ['nrp' => '61260004'])
            ->assertRedirect(route('hospital.dashboard', $hospital->slug));

        $this->assertDatabaseHas('verification_logs', [
            'hospital_id' => $hospital->id,
            'nrp' => '61260004',
            'result' => 'active',
        ]);
    }

    public function test_lookup_logs_the_pic_phone_used_at_login(): void
    {
        config(['hasnurverif.otp.enabled' => false]);
        $hospital = $this->hospital();
        $hospital->contacts()->create([
            'label' => 'Depok',
            'phone' => '081999999999',
            'phone_normalized' => '6281999999999',
            'is_active' => true,
        ]);

        $this->mock(EmployeeLookupService::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn([
                'result' => 'active',
                'feedback' => 'Karyawan Aktif',
                'nrp' => '61260004',
                'employee_name' => 'Eriton Latti Dewa',
                'group_company' => 'SBU Mining',
                'company_name' => 'PT. Graha Nusa Minergi',
            ]);
        });

        $this->post(route('hospital.otp.request', $hospital->slug), ['phone' => '081999999999'])
            ->assertRedirect(route('hospital.dashboard', $hospital->slug));

        $this->post(route('hospital.lookup', $hospital->slug), ['nrp' => '61260004'])
            ->assertRedirect(route('hospital.dashboard', $hospital->slug));

        $this->assertDatabaseHas('verification_logs', [
            'hospital_id' => $hospital->id,
            'hospital_name' => 'RS Contoh',
            'nrp' => '61260004',
            'pic_phone' => '081999999999',
            'pic_label' => 'Depok',
        ]);
    }

    public function test_deleting_hospital_keeps_log_snapshots(): void
    {
        config(['hasnurverif.otp.enabled' => false]);
        $hospital = $this->hospital();

        $this->mock(EmployeeLookupService::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn([
                'result' => 'active',
                'feedback' => 'Karyawan Aktif',
                'nrp' => '61260004',
                'employee_name' => 'Eriton Latti Dewa',
                'group_company' => 'SBU Mining',
                'company_name' => 'PT. Graha Nusa Minergi',
            ]);
        });

        $this->post(route('hospital.otp.request', $hospital->slug), ['phone' => '081234567890']);
        $this->post(route('hospital.lookup', $hospital->slug), ['nrp' => '61260004']);

        $hospital->delete();

        $log = VerificationLog::latest()->first();
        $this->assertSame('RS Contoh', $log->hospital_name);
        $this->assertNull($log->hospital);

        $loginLog = HospitalLoginLog::latest()->first();
        $this->assertSame('RS Contoh', $loginLog->hospital_name);
    }

    public function test_not_found_lookup_is_logged(): void
    {
        $hospital = $this->hospital();
        $this->actingAs($hospital, 'hospital');

        $this->mock(EmployeeLookupService::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn([
                'result' => 'not_found',
                'feedback' => 'NRP tidak ditemukan',
                'nrp' => '00000',
                'employee_name' => null,
                'group_company' => null,
                'company_name' => null,
            ]);
        });

        $this->post(route('hospital.lookup', $hospital->slug), ['nrp' => '00000'])
            ->assertRedirect(route('hospital.dashboard', $hospital->slug));

        $this->assertDatabaseHas('verification_logs', [
            'nrp' => '00000',
            'result' => 'not_found',
        ]);
    }

    public function test_hospital_cannot_access_other_hospital_link(): void
    {
        $a = $this->hospital();
        $b = $this->hospital(['name' => 'RS Lain', 'slug' => 'rs-lain', 'pic_phone' => '0899']);

        $this->actingAs($a, 'hospital');
        $this->get(route('hospital.dashboard', $b->slug))
            ->assertRedirect(route('hospital.login', $b->slug));
    }

    public function test_inactive_hospital_link_is_blocked(): void
    {
        $hospital = $this->hospital(['is_active' => false]);

        $this->get(route('hospital.login', $hospital->slug))->assertStatus(403);
    }

    public function test_unknown_hospital_slug_returns_404(): void
    {
        $this->get('/rs/tidak-ada')->assertStatus(404);
    }
}
