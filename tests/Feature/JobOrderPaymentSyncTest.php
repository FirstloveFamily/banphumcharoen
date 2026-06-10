<?php

namespace Tests\Feature;

use App\Models\Employer;
use App\Models\JobOrder;
use App\Models\JobOrderPayment;
use App\Models\Nationality;
use App\Models\Service;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobOrderPaymentSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_payments_update_paid_amount_payment_status_and_remaining_amount(): void
    {
        $jobOrder = $this->createJobOrder(serviceFee: 10000);

        JobOrderPayment::create([
            'job_order_id' => $jobOrder->id,
            'amount' => 3000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'transfer',
            'status' => 'pending',
        ]);

        $jobOrder->refresh();

        $this->assertSame('0.00', $jobOrder->paid_amount);
        $this->assertSame('pending', $jobOrder->payment_status);
        $this->assertSame(10000.0, $jobOrder->getRemainingAmount());

        JobOrderPayment::create([
            'job_order_id' => $jobOrder->id,
            'amount' => 3000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'transfer',
            'status' => 'verified',
        ]);

        $jobOrder->refresh();

        $this->assertSame('3000.00', $jobOrder->paid_amount);
        $this->assertSame('partial', $jobOrder->payment_status);
        $this->assertSame(7000.0, $jobOrder->getRemainingAmount());

        JobOrderPayment::create([
            'job_order_id' => $jobOrder->id,
            'amount' => 7000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'transfer',
            'status' => 'verified',
        ]);

        $jobOrder->refresh();

        $this->assertSame('10000.00', $jobOrder->paid_amount);
        $this->assertSame('paid', $jobOrder->payment_status);
        $this->assertSame(0.0, $jobOrder->getRemainingAmount());
    }

    public function test_rejected_or_deleted_payments_are_not_counted_as_paid(): void
    {
        $jobOrder = $this->createJobOrder(serviceFee: 5000);

        $payment = JobOrderPayment::create([
            'job_order_id' => $jobOrder->id,
            'amount' => 5000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'transfer',
            'status' => 'verified',
        ]);

        $payment->update(['status' => 'rejected']);
        $jobOrder->refresh();

        $this->assertSame('0.00', $jobOrder->paid_amount);
        $this->assertSame('pending', $jobOrder->payment_status);

        $payment->update(['status' => 'verified']);
        $payment->delete();
        $jobOrder->refresh();

        $this->assertSame('0.00', $jobOrder->paid_amount);
        $this->assertSame('pending', $jobOrder->payment_status);
    }

    private function createJobOrder(float $serviceFee): JobOrder
    {
        $employer = Employer::create([
            'company_code' => 'EMP001',
            'company_name' => 'Acme Co., Ltd.',
            'contact_name' => 'Accounting',
            'phone' => '020000000',
            'email' => 'accounting@example.test',
            'is_active' => true,
        ]);

        $nationality = Nationality::create([
            'name_th' => 'เมียนมา',
            'name_en' => 'Myanmar',
            'country_code' => 'MM',
            'is_active' => true,
        ]);

        $worker = Worker::create([
            'employer_id' => $employer->id,
            'nationality_id' => $nationality->id,
            'first_name_th' => 'ทดสอบ',
            'last_name_th' => 'ระบบ',
            'first_name_en' => 'Test',
            'last_name_en' => 'Worker',
            'birth_date' => '1990-01-01',
            'passport_number' => 'P000001',
            'passport_expiry' => now()->addYear()->toDateString(),
            'is_active' => true,
        ]);

        $service = Service::create([
            'name' => 'Visa Extension',
            'code' => 'VISA',
            'alert_days_before_expiry' => 30,
            'is_active' => true,
        ]);

        return JobOrder::create([
            'employer_id' => $employer->id,
            'worker_id' => $worker->id,
            'service_id' => $service->id,
            'service_fee' => $serviceFee,
            'paid_amount' => 0,
            'payment_status' => 'pending',
            'status' => 'pending',
            'priority' => 'medium',
        ]);
    }
}
