<?php

namespace App\Http\Controllers;

use App\Models\Nationality;
use App\Models\WorkerPrefix;
use App\Models\WorkerRegistrationRequest;
use Illuminate\Http\Request;

class EmployerWorkerRequestController extends Controller
{
    public function create(Request $request)
    {
        $this->authorizeEmployer($request);

        $nationalities = Nationality::query()->orderBy('name_th')->get();
        $workerPrefixes = WorkerPrefix::query()->active()->orderBy('sort_order')->orderBy('name_th')->get();

        return view('staff-portal.workers.create', [
            'nationalities' => $nationalities,
            'workerPrefixes' => $workerPrefixes,
            'employers' => collect(),
            'requestMode' => true,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeEmployer($request);

        $employer = $request->user()->employers()->orderBy('company_name')->firstOrFail();
        $validated = $request->validate([
            'nationality_id' => ['required', 'exists:nationalities,id'],
            'worker_prefix_id' => ['nullable', 'exists:worker_prefixes,id'],
            'first_name_th' => ['required', 'string', 'max:150'],
            'last_name_th' => ['nullable', 'string', 'max:150'],
            'first_name_en' => ['required', 'string', 'max:150'],
            'last_name_en' => ['nullable', 'string', 'max:150'],
            'birth_date' => ['required', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'passport_number' => ['nullable', 'string', 'max:100'],
            'passport_expiry' => ['nullable', 'date'],
            'pink_card_number' => ['nullable', 'string', 'max:100'],
            'pink_card_expiry' => ['nullable', 'date'],
            'wp_number' => ['nullable', 'string', 'max:100'],
            'wp_expiry' => ['nullable', 'date'],
            'visa_expiry' => ['nullable', 'date'],
            'report_90_days_due' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:2000'],
            'passport_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'pink_card_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'wp_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'visa_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'report_90_days_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'photo_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $requestData = collect($validated)->except([
            'passport_file', 'pink_card_file', 'wp_file', 'visa_file', 'report_90_days_file', 'photo_file',
        ])->all();

        $registrationRequest = WorkerRegistrationRequest::create([
            'employer_id' => $employer->id,
            'requested_by' => $request->user()->id,
            'status' => 'pending',
            'data' => $requestData,
        ]);

        foreach (['passport_file', 'pink_card_file', 'wp_file', 'visa_file', 'report_90_days_file', 'photo_file'] as $field) {
            if ($request->hasFile($field)) {
                $requestData[$field] = $request->file($field)->store("worker-registration-requests/{$registrationRequest->id}", 'public');
            }
        }

        $registrationRequest->update(['data' => $requestData]);

        return redirect()->route('employers.workers.index')
            ->with('success', 'ส่งคำขอเพิ่มแรงงานให้เจ้าหน้าที่ตรวจสอบแล้ว');
    }

    private function authorizeEmployer(Request $request): void
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasAnyRole(['employer', 'hr']) || in_array($user->role ?? null, ['employer', 'hr'], true)),
            403
        );
    }
}
