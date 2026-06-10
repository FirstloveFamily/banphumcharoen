<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\Nationality;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffPortalJobOrderWorkerFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_select_starts_disabled_until_an_employer_is_selected(): void
    {
        $staff = User::factory()->create();
        Role::findOrCreate('staff', 'web');
        $staff->assignRole('staff');

        $employer = Employer::create([
            'company_code' => 'EMP-001',
            'company_name' => 'Alpha Co., Ltd.',
            'contact_name' => 'Alpha Contact',
            'phone' => '020000000',
            'email' => 'alpha@example.test',
            'is_active' => true,
        ]);

        $otherEmployer = Employer::create([
            'company_code' => 'EMP-002',
            'company_name' => 'Beta Co., Ltd.',
            'contact_name' => 'Beta Contact',
            'phone' => '020000001',
            'email' => 'beta@example.test',
            'is_active' => true,
        ]);

        $nationality = Nationality::create([
            'name_th' => 'เมียนมา',
            'name_en' => 'Myanmar',
            'country_code' => 'MM',
            'is_active' => true,
        ]);

        Worker::create([
            'employer_id' => $employer->id,
            'nationality_id' => $nationality->id,
            'first_name_th' => 'สมชาย',
            'last_name_th' => 'ใจดี',
            'first_name_en' => 'Somchai',
            'last_name_en' => 'Jaidee',
            'birth_date' => '1990-01-01',
            'passport_number' => 'P000001',
            'passport_expiry' => '2028-01-01',
            'is_active' => true,
        ]);

        Worker::create([
            'employer_id' => $otherEmployer->id,
            'nationality_id' => $nationality->id,
            'first_name_th' => 'สมหญิง',
            'last_name_th' => 'ดีมาก',
            'first_name_en' => 'Somying',
            'last_name_en' => 'Deemak',
            'birth_date' => '1991-01-01',
            'passport_number' => 'P000002',
            'passport_expiry' => '2028-01-01',
            'is_active' => true,
        ]);

        Service::create([
            'name' => 'Visa Extension',
            'code' => 'VISA',
            'alert_days_before_expiry' => 30,
            'is_active' => true,
        ]);

        $response = $this->actingAs($staff)
            ->get(route('staff.portal.job-orders.create'));

        $response->assertOk()
            ->assertSee('data-employer-id="' . $employer->id . '"', false)
            ->assertSee('data-employer-id="' . $otherEmployer->id . '"', false);

        $this->assertMatchesRegularExpression('/<select[^>]*id="worker_id"[^>]*disabled/', $response->getContent());

        $responseWithEmployer = $this->actingAs($staff)
            ->get(route('staff.portal.job-orders.create', ['employer_id' => $employer->id]));

        $responseWithEmployer->assertOk()
            ->assertSee('value="' . $employer->id . '" selected', false);

        $this->assertDoesNotMatchRegularExpression('/<select[^>]*id="worker_id"[^>]*disabled/', $responseWithEmployer->getContent());
    }
}
