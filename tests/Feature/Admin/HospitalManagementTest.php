<?php

namespace Tests\Feature\Admin;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HospitalManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'employee_id' => '61260004',
            'name' => 'Admin Test',
            'email' => 'admin@example.com',
            'is_super_admin' => true,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_hospital(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.hospitals.store'), [
                'name' => 'RS Sehat Sentosa',
                'slug' => 'rs-sehat-sentosa',
                'detail' => 'Catatan',
                'is_active' => '1',
                'contacts' => [
                    ['label' => 'Pusat', 'phone' => '081234567890'],
                ],
            ])
            ->assertRedirect(route('admin.hospitals.index'));

        $this->assertDatabaseHas('hospitals', [
            'slug' => 'rs-sehat-sentosa',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('hospital_contacts', ['phone_normalized' => '6281234567890']);
    }

    public function test_admin_can_store_multiple_contacts(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.hospitals.store'), [
                'name' => 'RS Mitra Keluarga',
                'slug' => 'rs-mitra-keluarga',
                'contacts' => [
                    ['label' => 'Pamulang', 'phone' => '081200000001'],
                    ['label' => 'Depok', 'phone' => '081200000002'],
                ],
            ]);

        $hospital = Hospital::where('slug', 'rs-mitra-keluarga')->firstOrFail();
        $this->assertCount(2, $hospital->contacts);
        $this->assertSame('6281200000001', $hospital->contacts->first()->phone_normalized);
    }

    public function test_slug_is_auto_generated_from_name(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.hospitals.store'), [
                'name' => 'RSUD Banjarmasin',
                'contacts' => [
                    ['phone' => '081200000000'],
                ],
            ]);

        $this->assertDatabaseHas('hospitals', ['slug' => 'rsud-banjarmasin']);
    }

    public function test_slug_must_be_unique(): void
    {
        Hospital::create([
            'name' => 'RS A',
            'slug' => 'rs-a',
            'pic_phone' => '0811',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.hospitals.store'), [
                'name' => 'RS B',
                'slug' => 'rs-a',
                'contacts' => [
                    ['phone' => '0822'],
                ],
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_admin_can_deactivate_hospital(): void
    {
        $hospital = Hospital::create([
            'name' => 'RS Aktif',
            'slug' => 'rs-aktif',
            'pic_phone' => '0811',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)->put(route('admin.hospitals.update', $hospital), [
            'name' => 'RS Aktif',
            'slug' => 'rs-aktif',
            'contacts' => [
                ['phone' => '0811'],
            ],
            // is_active tidak dikirim -> false
        ]);

        $this->assertFalse($hospital->fresh()->is_active);
    }

    public function test_admin_can_soft_delete_hospital(): void
    {
        $hospital = Hospital::create([
            'name' => 'RS Hapus',
            'slug' => 'rs-hapus',
            'pic_phone' => '0811',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.hospitals.destroy', $hospital))
            ->assertRedirect(route('admin.hospitals.index'));

        $this->assertSoftDeleted('hospitals', ['id' => $hospital->id]);
    }

    public function test_guest_cannot_access_admin_area(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
