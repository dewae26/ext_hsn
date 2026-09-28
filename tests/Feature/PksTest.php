<?php

namespace Tests\Feature;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PksTest extends TestCase
{
    use RefreshDatabase;

    protected function hospitalWithPks(): Hospital
    {
        Storage::fake('local');
        Storage::disk('local')->put('pks/sample.pdf', '%PDF-1.4 sample');

        return Hospital::create([
            'name' => 'RS PKS',
            'slug' => 'rs-pks',
            'pic_phone' => '081234567890',
            'pks_path' => 'pks/sample.pdf',
            'is_active' => true,
        ]);
    }

    protected function admin(): User
    {
        return User::create([
            'employee_id' => '61260004',
            'name' => 'Admin',
            'is_super_admin' => true,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_pks_inline(): void
    {
        $hospital = $this->hospitalWithPks();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.hospitals.pks', $hospital));

        $response->assertOk();
        $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_download_pks(): void
    {
        $hospital = $this->hospitalWithPks();

        $response = $this->actingAs($this->admin())
            ->get(route('admin.hospitals.pks.download', $hospital));

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    public function test_hospital_can_view_pks_inline(): void
    {
        $hospital = $this->hospitalWithPks();

        $response = $this->actingAs($hospital, 'hospital')
            ->get(route('hospital.pks', $hospital->slug));

        $response->assertOk();
        $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));
    }

    public function test_edit_page_shows_delete_pks_button(): void
    {
        $hospital = $this->hospitalWithPks();

        $this->actingAs($this->admin())
            ->get(route('admin.hospitals.edit', $hospital))
            ->assertOk()
            ->assertSee('Hapus PKS')
            ->assertSee('deletePksForm');
    }

    public function test_admin_can_delete_pks(): void
    {
        $hospital = $this->hospitalWithPks();

        $this->actingAs($this->admin())
            ->delete(route('admin.hospitals.pks.destroy', $hospital))
            ->assertRedirect(route('admin.hospitals.edit', $hospital));

        $this->assertNull($hospital->fresh()->pks_path);
        Storage::disk('local')->assertMissing('pks/sample.pdf');
    }

    public function test_pks_missing_returns_404(): void
    {
        $hospital = Hospital::create([
            'name' => 'RS Tanpa PKS',
            'slug' => 'rs-tanpa-pks',
            'pic_phone' => '0811',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.hospitals.pks', $hospital))
            ->assertNotFound();
    }
}
