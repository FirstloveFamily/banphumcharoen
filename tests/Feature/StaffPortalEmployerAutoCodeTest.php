<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffPortalEmployerAutoCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_portal_create_page_shows_auto_generated_company_code_and_store_uses_it(): void
    {
        $staff = User::factory()->create();
        Role::findOrCreate('staff', 'web');
        $staff->assignRole('staff');

        $this->actingAs($staff)
            ->get(route('staff.portal.employers.create'))
            ->assertOk()
            ->assertSee('EMP-001');

        $this->actingAs($staff)
            ->post(route('staff.portal.employers.store'), [
                'company_code' => 'MANUAL-999',
                'company_name' => 'Acme Co., Ltd.',
                'contact_name' => 'Accounting',
                'phone' => '020000000',
                'email' => 'accounting@example.test',
                'is_active' => 1,
            ])
            ->assertRedirect(route('staff.portal.employers.index'));

        $this->assertDatabaseHas('employers', [
            'company_code' => 'EMP-001',
            'company_name' => 'Acme Co., Ltd.',
        ]);

        $this->actingAs($staff)
            ->get(route('staff.portal.employers.create'))
            ->assertOk()
            ->assertSee('EMP-002');

        $this->assertSame(1, Employer::count());
    }
}
