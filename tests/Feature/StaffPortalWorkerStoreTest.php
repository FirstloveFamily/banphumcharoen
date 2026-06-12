<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Nationality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffPortalWorkerStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_portal_can_store_a_worker_with_full_identity_fields(): void
    {
        $staff = User::factory()->create();
        Role::findOrCreate('staff', 'web');
        $staff->assignRole('staff');

        $employer = Employer::create([
            'company_code' => 'EMP-001',
            'company_name' => 'Acme Co., Ltd.',
            'contact_name' => 'HR Team',
            'phone' => '020000000',
            'email' => 'hr@example.test',
            'is_active' => true,
        ]);

        $nationality = Nationality::create([
            'name_th' => 'ไทย',
            'name_en' => 'Thai',
            'country_code' => 'TH',
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.portal.workers.store'), [
                'employer_id' => $employer->id,
                'nationality_id' => $nationality->id,
                'is_active' => 1,
                'first_name_th' => 'สมชาย',
                'last_name_th' => 'ใจดี',
                'first_name_en' => 'Somchai',
                'last_name_en' => 'Jaidee',
                'birth_date' => '2000-12-12',
                'gender' => 'male',
                'passport_number' => 'P1234567',
                'passport_expiry' => '2030-12-31',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('workers', [
            'employer_id' => $employer->id,
            'nationality_id' => $nationality->id,
            'first_name_th' => 'สมชาย',
            'last_name_th' => 'ใจดี',
            'first_name_en' => 'Somchai',
            'last_name_en' => 'Jaidee',
            'passport_number' => 'P1234567',
        ]);
    }

    public function test_staff_portal_can_store_a_worker_without_optional_identity_and_document_fields(): void
    {
        $staff = User::factory()->create();
        Role::findOrCreate('staff', 'web');
        $staff->assignRole('staff');

        $employer = Employer::create([
            'company_code' => 'EMP-001',
            'company_name' => 'Acme Co., Ltd.',
            'contact_name' => 'HR Team',
            'phone' => '020000000',
            'email' => 'hr@example.test',
            'is_active' => true,
        ]);

        $nationality = Nationality::create([
            'name_th' => 'ไทย',
            'name_en' => 'Thai',
            'country_code' => 'TH',
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.portal.workers.store'), [
                'employer_id' => $employer->id,
                'nationality_id' => $nationality->id,
                'is_active' => 1,
                'first_name_th' => 'สมชาย',
                'first_name_en' => 'Somchai',
                'birth_date' => '2000-12-12',
                'gender' => 'male',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('workers', [
            'employer_id' => $employer->id,
            'nationality_id' => $nationality->id,
            'first_name_th' => 'สมชาย',
            'last_name_th' => null,
            'first_name_en' => 'Somchai',
            'last_name_en' => null,
            'passport_number' => null,
            'passport_expiry' => null,
            'wp_number' => null,
            'wp_expiry' => null,
            'visa_expiry' => null,
            'report_90_days_due' => null,
        ]);
    }
}
