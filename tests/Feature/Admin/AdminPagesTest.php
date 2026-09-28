<?php

namespace Tests\Feature\Admin;

use App\Models\Hospital;
use App\Models\User;
use App\Models\VerificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function superAdmin(): User
    {
        return User::create([
            'employee_id' => '61260004',
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'is_super_admin' => true,
            'is_active' => true,
        ]);
    }

    public function test_all_admin_pages_render(): void
    {
        $admin = $this->superAdmin();
        $hospital = Hospital::create([
            'name' => 'RS Render',
            'slug' => 'rs-render',
            'pic_phone' => '0811',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('hv-avatar')
            ->assertSee('SA')
            ->assertSee('HASNUR GROUP');
        $this->get(route('admin.hospitals.index'))->assertOk();
        $this->get(route('admin.hospitals.create'))->assertOk();
        $this->get(route('admin.hospitals.edit', $hospital))->assertOk();
        $this->get(route('admin.logs.verifications'))->assertOk();
        $this->get(route('admin.logs.logins'))->assertOk();
        $this->get(route('admin.logs.export'))->assertOk();
        $this->get(route('admin.admins.index'))->assertOk();
    }

    public function test_non_super_admin_cannot_manage_admins(): void
    {
        $user = User::create([
            'employee_id' => '111',
            'name' => 'Admin Biasa',
            'is_active' => true,
            'is_super_admin' => false,
        ]);

        $this->actingAs($user)->get(route('admin.admins.index'))->assertForbidden();
    }

    public function test_dashboard_shows_chart_and_ranking_with_data(): void
    {
        $admin = $this->superAdmin();
        $hospital = Hospital::create([
            'name' => 'RS Grafik',
            'slug' => 'rs-grafik',
            'pic_phone' => '0811',
            'is_active' => true,
        ]);

        VerificationLog::create([
            'hospital_id' => $hospital->id,
            'nrp' => '61260004',
            'employee_name' => 'Eriton',
            'group_company' => 'SBU Mining',
            'company_name' => 'PT. Graha Nusa Minergi',
            'result' => 'active',
            'feedback' => 'Karyawan Aktif',
            'ip' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['days' => 7]))
            ->assertOk()
            ->assertSee('verificationChart')
            ->assertSee('RS Grafik')
            ->assertSee('Nomor PIC');

        $this->actingAs($admin)->get(route('admin.dashboard', ['days' => 30]))->assertOk();
    }

    public function test_dashboard_keeps_data_of_deleted_hospital(): void
    {
        $admin = $this->superAdmin();
        $hospital = Hospital::create([
            'name' => 'RS Lama Dihapus',
            'slug' => 'rs-lama-dihapus',
            'pic_phone' => '0811',
            'is_active' => true,
        ]);

        VerificationLog::create([
            'hospital_id' => $hospital->id,
            'hospital_name' => 'RS Lama Dihapus',
            'nrp' => '61260004',
            'result' => 'active',
            'feedback' => 'Karyawan Aktif',
            'ip' => '127.0.0.1',
        ]);

        $hospital->delete();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('RS Lama Dihapus');
    }

    public function test_hospital_dashboard_renders_after_login(): void
    {
        config(['hasnurverif.otp.enabled' => false]);

        $hospital = Hospital::create([
            'name' => 'RS Dashboard',
            'slug' => 'rs-dashboard',
            'pic_phone' => '081234567890',
            'is_active' => true,
        ]);
        $hospital->contacts()->create([
            'label' => 'PIC',
            'phone' => '081234567890',
            'phone_normalized' => '6281234567890',
            'is_active' => true,
        ]);

        $this->post(route('hospital.otp.request', $hospital->slug), ['phone' => '081234567890'])
            ->assertRedirect(route('hospital.dashboard', $hospital->slug));

        $this->get(route('hospital.dashboard', $hospital->slug))
            ->assertOk()
            ->assertSee('NRP Karyawan');
    }
}
