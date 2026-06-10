<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Nationality;
use App\Models\Employer;
use App\Models\Worker;
use App\Models\Service;
use App\Models\ServiceChecklist;
use App\Models\DocumentMaster;
use App\Models\JobOrder;
use App\Models\JobOrderChecklist;
use App\Models\JobOrderPayment;
use App\Models\WorkerDocument;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users (at least 5 users)
        $usersData = [
            [
                'email' => 'admin@vcs.com',
                'name' => 'Admin VCS',
                'password' => Hash::make('admin'),
                'role' => 'super_admin',
            ],
            [
                'email' => 'somchai.s@vcs.com',
                'name' => 'สมชาย สายตรวจ',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
            ],
            [
                'email' => 'somsri.m@vcs.com',
                'name' => 'สมศรี มั่งมี',
                'password' => Hash::make('12345678'),
                'role' => 'staff',
            ],
            [
                'email' => 'wichai.k@vcs.com',
                'name' => 'วิชัย แก้วกล้า',
                'password' => Hash::make('12345678'),
                'role' => 'staff',
            ],
            [
                'email' => 'anong.p@vcs.com',
                'name' => 'อนงค์ ปรีชา',
                'password' => Hash::make('12345678'),
                'role' => 'staff',
            ],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $users[] = User::updateOrCreate(['email' => $data['email']], $data);
        }

        // 2. Nationalities (at least 5)
        $nationalitiesData = [
            [
                'country_code' => 'MM',
                'name_th' => 'เมียนมา',
                'name_en' => 'Myanmar',
                'icon_flag' => 'MM',
                'is_active' => true,
            ],
            [
                'country_code' => 'KH',
                'name_th' => 'กัมพูชา',
                'name_en' => 'Cambodia',
                'icon_flag' => 'KH',
                'is_active' => true,
            ],
            [
                'country_code' => 'LA',
                'name_th' => 'ลาว',
                'name_en' => 'Laos',
                'icon_flag' => 'LA',
                'is_active' => true,
            ],
            [
                'country_code' => 'VN',
                'name_th' => 'เวียดนาม',
                'name_en' => 'Vietnam',
                'icon_flag' => 'VN',
                'is_active' => true,
            ],
            [
                'country_code' => 'TH',
                'name_th' => 'ไทย',
                'name_en' => 'Thailand',
                'icon_flag' => 'TH',
                'is_active' => true,
            ],
        ];

        $nationalities = [];
        foreach ($nationalitiesData as $data) {
            $nationalities[] = Nationality::updateOrCreate(['country_code' => $data['country_code']], $data);
        }

        // 3. Document Master (at least 5)
        $documentMastersData = [
            [
                'code' => 'PASSPORT',
                'name' => 'หนังสือเดินทาง (Passport)',
                'description' => 'หนังสือเดินทางของแรงงานต่างด้าว',
                'is_active' => true,
            ],
            [
                'code' => 'WORK_PERMIT',
                'name' => 'ใบอนุญาตทำงาน (Work Permit)',
                'description' => 'ใบอนุญาตทำงานในประเทศไทย',
                'is_active' => true,
            ],
            [
                'code' => 'VISA',
                'name' => 'วีซ่าทำงาน (Non-L-A Visa)',
                'description' => 'วีซ่าทำงานที่ได้รับการตรวจลงตรา',
                'is_active' => true,
            ],
            [
                'code' => 'REPORT_90',
                'name' => 'ใบรายงานตัว 90 วัน (90 Days Report)',
                'description' => 'เอกสารยืนยันการรายงานตัวทุก 90 วัน',
                'is_active' => true,
            ],
            [
                'code' => 'HEALTH_CERT',
                'name' => 'ใบรับรองแพทย์ (Health Certificate)',
                'description' => 'ใบรับรองแพทย์สำหรับยื่นขอใบอนุญาตทำงาน',
                'is_active' => true,
            ],
        ];

        $documentMasters = [];
        foreach ($documentMastersData as $data) {
            $documentMasters[] = DocumentMaster::updateOrCreate(['code' => $data['code']], $data);
        }

        // 4. Employers (at least 5)
        $employersData = [
            [
                'company_code' => 'EMP-001',
                'company_name' => 'บริษัท เอบีซี คอนสตรัคชั่น จำกัด',
                'contact_name' => 'คุณสมศักดิ์ รักงาน',
                'phone' => '0812345678',
                'email' => 'somsak@abc-const.com',
                'tax_id' => '1234567890123',
                'address' => '99/9 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพมหานคร 10110',
                'is_active' => true,
                'notes' => 'ลูกค้าโครงการก่อสร้างขนาดใหญ่',
            ],
            [
                'company_code' => 'EMP-002',
                'company_name' => 'บริษัท รักดี บริการ จำกัด',
                'contact_name' => 'คุณพิมลพรรณ ใจดี',
                'phone' => '0823456789',
                'email' => 'pimol@rakdee-service.com',
                'tax_id' => '2345678901234',
                'address' => '123 อาคารสาทรทาวเวอร์ ถนนสาทรใต้ แขวงยานนาวา เขตสาทร กรุงเทพมหานคร 10120',
                'is_active' => true,
                'notes' => 'กลุ่มธุรกิจบริการและทำความสะอาด',
            ],
            [
                'company_code' => 'EMP-003',
                'company_name' => 'ร้านอาหาร อร่อยดี (สาขาลาดพร้าว)',
                'contact_name' => 'คุณแม่ครัว อร่อยมาก',
                'phone' => '0834567890',
                'email' => 'aroydee@gmail.com',
                'tax_id' => '3456789012345',
                'address' => '456 ถนนลาดพร้าว แขวงจอมพล เขตจตุจักร กรุงเทพมหานคร 10900',
                'is_active' => true,
                'notes' => 'ร้านอาหารไทย-จีน มีแรงงานเมียนมาและลาว',
            ],
            [
                'company_code' => 'EMP-004',
                'company_name' => 'บริษัท เอสพี โลจิสติกส์ จำกัด',
                'contact_name' => 'คุณเกรียงไกร ขนส่ง',
                'phone' => '0845678901',
                'email' => 'kriengkrai@sp-logistics.co.th',
                'tax_id' => '4567890123456',
                'address' => '789 ถนนพระราม 3 แขวงบางโพงพาง เขตยานนาวา กรุงเทพมหานคร 10120',
                'is_active' => true,
                'notes' => 'บริษัทขนส่งสินค้าคลังสินค้าขนาดกลาง',
            ],
            [
                'company_code' => 'EMP-005',
                'company_name' => 'บริษัท สมาร์ท การ์เมนท์ จำกัด',
                'contact_name' => 'คุณนารี เย็บผ้า',
                'phone' => '0856789012',
                'email' => 'naree@smartgarment.co.th',
                'tax_id' => '5678901234567',
                'address' => '321 ถนนเพชรเกษม แขวงบางหว้า เขตภาษีเจริญ กรุงเทพมหานคร 10160',
                'is_active' => true,
                'notes' => 'โรงงานสิ่งทอและเครื่องนุ่งห่ม มีแรงงานต่างด้าวกว่า 100 คน',
            ],
        ];

        $employers = [];
        foreach ($employersData as $data) {
            $emp = Employer::updateOrCreate(['company_code' => $data['company_code']], $data);
            $employers[] = $emp;
            
            // Connect this employer with some admin/staff users (pivot table employer_user)
            $randomUser = $users[rand(0, count($users) - 1)];
            $emp->users()->syncWithoutDetaching([
                $randomUser->id => ['role' => ['owner', 'hr', 'accounting', 'viewer'][rand(0, 3)]]
            ]);
        }

        // 5. Workers (at least 5)
        $workersData = [
            [
                'passport_number' => 'CC1234567',
                'employer_id' => $employers[0]->id, // ABC Construction
                'nationality_id' => $nationalities[0]->id, // Myanmar
                'prefix_th' => 'นาย',
                'first_name_th' => 'หม่อง',
                'last_name_th' => 'มินต์',
                'prefix_en' => 'Mr.',
                'first_name_en' => 'Maung',
                'last_name_en' => 'Myint',
                'birth_date' => '1995-05-12',
                'gender' => 'male',
                'passport_expiry' => '2028-10-20',
                'wp_number' => 'WP-MM-001',
                'wp_expiry' => '2026-12-31',
                'visa_expiry' => '2027-02-15',
                'report_90_days_due' => '2026-08-10',
                'is_active' => true,
            ],
            [
                'passport_number' => 'N67890123',
                'employer_id' => $employers[1]->id, // Rakdee Services
                'nationality_id' => $nationalities[1]->id, // Cambodia
                'prefix_th' => 'นางสาว',
                'first_name_th' => 'จัน',
                'last_name_th' => 'ทา',
                'prefix_en' => 'Ms.',
                'first_name_en' => 'Chan',
                'last_name_en' => 'Tha',
                'birth_date' => '1998-08-25',
                'gender' => 'female',
                'passport_expiry' => '2029-04-14',
                'wp_number' => 'WP-KH-002',
                'wp_expiry' => '2027-01-20',
                'visa_expiry' => '2027-03-10',
                'report_90_days_due' => '2026-09-05',
                'is_active' => true,
            ],
            [
                'passport_number' => 'P99887766',
                'employer_id' => $employers[2]->id, // Aroy Dee Restaurant
                'nationality_id' => $nationalities[2]->id, // Laos
                'prefix_th' => 'นาย',
                'first_name_th' => 'ท้าว',
                'last_name_th' => 'เพชร',
                'prefix_en' => 'Mr.',
                'first_name_en' => 'Thao',
                'last_name_en' => 'Phet',
                'birth_date' => '1992-01-30',
                'gender' => 'male',
                'passport_expiry' => '2027-11-05',
                'wp_number' => 'WP-LA-003',
                'wp_expiry' => '2026-11-30',
                'visa_expiry' => '2026-12-15',
                'report_90_days_due' => '2026-07-22',
                'is_active' => true,
            ],
            [
                'passport_number' => 'CC9876543',
                'employer_id' => $employers[3]->id, // SP Logistics
                'nationality_id' => $nationalities[0]->id, // Myanmar
                'prefix_th' => 'นาย',
                'first_name_th' => 'จ่อ',
                'last_name_th' => 'ซู',
                'prefix_en' => 'Mr.',
                'first_name_en' => 'Kyaw',
                'last_name_en' => 'Thu',
                'birth_date' => '1990-11-14',
                'gender' => 'male',
                'passport_expiry' => '2030-01-10',
                'wp_number' => 'WP-MM-004',
                'wp_expiry' => '2027-06-30',
                'visa_expiry' => '2027-08-15',
                'report_90_days_due' => '2026-10-01',
                'is_active' => true,
            ],
            [
                'passport_number' => 'V55443322',
                'employer_id' => $employers[4]->id, // Smart Garment
                'nationality_id' => $nationalities[3]->id, // Vietnam
                'prefix_th' => 'นาง',
                'first_name_th' => 'เหงียน',
                'last_name_th' => 'ถิ บา',
                'prefix_en' => 'Mrs.',
                'first_name_en' => 'Nguyen',
                'last_name_en' => 'Thi Ba',
                'birth_date' => '1987-03-05',
                'gender' => 'female',
                'passport_expiry' => '2028-09-12',
                'wp_number' => 'WP-VN-005',
                'wp_expiry' => '2026-10-15',
                'visa_expiry' => '2026-11-01',
                'report_90_days_due' => '2026-07-15',
                'is_active' => true,
            ],
        ];

        $workers = [];
        foreach ($workersData as $data) {
            $workers[] = Worker::updateOrCreate(['passport_number' => $data['passport_number']], $data);
        }

        // 6. Worker Documents (at least 5)
        foreach ($workers as $index => $worker) {
            WorkerDocument::updateOrCreate(
                [
                    'worker_id' => $worker->id,
                    'document_master_id' => $documentMasters[0]->id, // Passport
                ],
                [
                    'file_path' => 'worker_docs/sample_passport_' . ($index + 1) . '.pdf',
                    'expiry_date' => $worker->passport_expiry,
                    'note' => 'สำเนาพาสปอร์ตเล่มปัจจุบัน',
                ]
            );

            WorkerDocument::updateOrCreate(
                [
                    'worker_id' => $worker->id,
                    'document_master_id' => $documentMasters[1]->id, // Work Permit
                ],
                [
                    'file_path' => 'worker_docs/sample_wp_' . ($index + 1) . '.pdf',
                    'expiry_date' => $worker->wp_expiry,
                    'note' => 'สำเนาใบอนุญาตทำงาน',
                ]
            );
        }

        // 7. Services (at least 5)
        $servicesData = [
            [
                'code' => 'WP_NEW',
                'name' => 'ทำใบอนุญาตทำงานใหม่',
                'description' => 'บริการยื่นคำขอรับใบอนุญาตทำงานครั้งแรกสำหรับคนต่างด้าว',
                'alert_days_before_expiry' => 45,
                'is_active' => true,
            ],
            [
                'code' => 'WP_RENEW',
                'name' => 'ต่ออายุใบอนุญาตทำงาน',
                'description' => 'บริการยื่นคำขอต่ออายุใบอนุญาตทำงานประจำปี',
                'alert_days_before_expiry' => 60,
                'is_active' => true,
            ],
            [
                'code' => 'REPORT_90_SVC',
                'name' => 'แจ้งรายงานตัว 90 วัน',
                'description' => 'บริการแจ้งที่พักอาศัยของคนต่างด้าวเมื่ออยู่ในประเทศครบ 90 วัน',
                'alert_days_before_expiry' => 15,
                'is_active' => true,
            ],
            [
                'code' => 'VISA_RENEW',
                'name' => 'ต่ออายุวีซ่าทำงาน (Non-L-A)',
                'description' => 'บริการยื่นขออนุญาตอยู่ต่อในราชอาณาจักรชั่วคราวเพื่อทำงาน',
                'alert_days_before_expiry' => 60,
                'is_active' => true,
            ],
            [
                'code' => 'IMPORT_MOU',
                'name' => 'นำเข้าแรงงานตามระบบ MOU',
                'description' => 'กระบวนการนำเข้าแรงงานต่างด้าวเข้ามาทำงานตามข้อตกลง MOU ระหว่างประเทศ',
                'alert_days_before_expiry' => 90,
                'is_active' => true,
            ],
        ];

        $services = [];
        foreach ($servicesData as $data) {
            $services[] = Service::updateOrCreate(['code' => $data['code']], $data);
        }

        // 8. Service Checklist
        $serviceChecklistMap = [
            'WP_NEW' => ['PASSPORT', 'HEALTH_CERT', 'VISA'],
            'WP_RENEW' => ['PASSPORT', 'WORK_PERMIT', 'HEALTH_CERT'],
            'REPORT_90_SVC' => ['PASSPORT', 'REPORT_90'],
            'VISA_RENEW' => ['PASSPORT', 'WORK_PERMIT', 'VISA'],
            'IMPORT_MOU' => ['PASSPORT', 'HEALTH_CERT'],
        ];

        foreach ($services as $service) {
            $codes = $serviceChecklistMap[$service->code] ?? ['PASSPORT'];
            $sort = 1;
            foreach ($codes as $code) {
                $docMaster = collect($documentMasters)->firstWhere('code', $code);
                if ($docMaster) {
                    ServiceChecklist::updateOrCreate(
                        [
                            'service_id' => $service->id,
                            'document_name' => $docMaster->name,
                        ],
                        [
                            'sort_order' => $sort++,
                            'is_required' => true,
                        ]
                    );
                }
            }
        }

        // 9. Job Orders (at least 5)
        $jobOrdersData = [
            [
                'job_number' => 'JOB-2026-0001',
                'employer_id' => $employers[0]->id, // ABC Construction
                'worker_id' => $workers[0]->id, // Maung Myint
                'service_id' => $services[1]->id, // WP_RENEW
                'assigned_user_id' => $users[2]->id, // Somsri (Staff)
                'service_fee' => 3500.00,
                'paid_amount' => 3500.00,
                'payment_status' => 'paid',
                'status' => 'completed',
                'priority' => 'medium',
                'due_date' => Carbon::now()->addDays(15)->toDateString(),
                'started_at' => Carbon::now()->subDays(10),
                'completed_at' => Carbon::now()->subDays(1),
                'notes' => 'ต่ออายุใบอนุญาตทำงานเรียบร้อย เอกสารครบถ้วน',
            ],
            [
                'job_number' => 'JOB-2026-0002',
                'employer_id' => $employers[1]->id, // Rakdee Services
                'worker_id' => $workers[1]->id, // Chan Tha
                'service_id' => $services[3]->id, // VISA_RENEW
                'assigned_user_id' => $users[3]->id, // Wichai (Staff)
                'service_fee' => 4500.00,
                'paid_amount' => 2000.00,
                'payment_status' => 'partial',
                'status' => 'processing',
                'priority' => 'high',
                'due_date' => Carbon::now()->addDays(7)->toDateString(),
                'started_at' => Carbon::now()->subDays(5),
                'notes' => 'อยู่ระหว่างรอคิวตรวจลงตราวีซ่าที่ ตม.',
            ],
            [
                'job_number' => 'JOB-2026-0003',
                'employer_id' => $employers[2]->id, // Aroy Dee Restaurant
                'worker_id' => $workers[2]->id, // Thao Phet
                'service_id' => $services[2]->id, // REPORT_90_SVC
                'assigned_user_id' => $users[4]->id, // Anong (Staff)
                'service_fee' => 800.00,
                'paid_amount' => 0.00,
                'payment_status' => 'pending',
                'status' => 'pending',
                'priority' => 'low',
                'due_date' => Carbon::now()->addDays(20)->toDateString(),
                'notes' => 'ลูกค้าฝากแจ้งรายงานตัว 90 วันล่วงหน้า',
            ],
            [
                'job_number' => 'JOB-2026-0004',
                'employer_id' => $employers[3]->id, // SP Logistics
                'worker_id' => $workers[3]->id, // Kyaw Thu
                'service_id' => $services[0]->id, // WP_NEW
                'assigned_user_id' => $users[2]->id, // Somsri (Staff)
                'service_fee' => 5000.00,
                'paid_amount' => 5000.00,
                'payment_status' => 'paid',
                'status' => 'waiting_document',
                'priority' => 'urgent',
                'due_date' => Carbon::now()->addDays(3)->toDateString(),
                'started_at' => Carbon::now()->subDays(2),
                'notes' => 'รอเอกสารใบรับรองแพทย์จากทางนายจ้าง',
            ],
            [
                'job_number' => 'JOB-2026-0005',
                'employer_id' => $employers[4]->id, // Smart Garment
                'worker_id' => $workers[4]->id, // Nguyen Thi Ba
                'service_id' => $services[4]->id, // IMPORT_MOU
                'assigned_user_id' => $users[1]->id, // Somchai (Admin)
                'service_fee' => 12000.00,
                'paid_amount' => 0.00,
                'payment_status' => 'pending',
                'status' => 'processing',
                'priority' => 'medium',
                'due_date' => Carbon::now()->addDays(45)->toDateString(),
                'started_at' => Carbon::now()->subDays(12),
                'notes' => 'เริ่มกระบวนการดีมานด์ที่กรมการจัดหางาน',
            ],
        ];

        $jobOrders = [];
        foreach ($jobOrdersData as $data) {
            $jo = JobOrder::updateOrCreate(['job_number' => $data['job_number']], $data);
            $jobOrders[] = $jo;

            // Seed Job Order Checklists
            $docMasters = [];
            if ($jo->service_id == $services[0]->id) {
                $docMasters = [$documentMasters[0], $documentMasters[4], $documentMasters[2]];
            } elseif ($jo->service_id == $services[1]->id) {
                $docMasters = [$documentMasters[0], $documentMasters[1], $documentMasters[4]];
            } elseif ($jo->service_id == $services[2]->id) {
                $docMasters = [$documentMasters[0], $documentMasters[3]];
            } elseif ($jo->service_id == $services[3]->id) {
                $docMasters = [$documentMasters[0], $documentMasters[1], $documentMasters[2]];
            } else {
                $docMasters = [$documentMasters[0], $documentMasters[4]];
            }

            foreach ($docMasters as $docMaster) {
                JobOrderChecklist::updateOrCreate(
                    [
                        'job_order_id' => $jo->id,
                        'document_master_id' => $docMaster->id,
                    ],
                    [
                        'is_required' => true,
                        'status' => $jo->status === 'completed' ? 'verified' : ($jo->status === 'pending' ? 'pending' : 'received'),
                        'received_at' => $jo->status === 'completed' || $jo->status === 'processing' ? Carbon::now()->subDays(4) : null,
                        'verified_by' => $jo->status === 'completed' ? $users[1]->id : null,
                        'verified_at' => $jo->status === 'completed' ? Carbon::now()->subDays(1) : null,
                    ]
                );
            }
        }

        // 10. Job Order Payments (at least 5 payments in total)
        $paymentsData = [
            [
                'job_order_id' => $jobOrders[0]->id, // JOB-2026-0001
                'amount' => 3500.00,
                'payment_date' => Carbon::now()->subDays(9)->toDateString(),
                'payment_method' => 'transfer',
                'payment_reference' => 'TXN987654321',
                'status' => 'verified',
                'received_by' => $users[1]->id,
                'note' => 'จ่ายครบถ้วน ยืนยันสลิปแล้ว',
            ],
            [
                'job_order_id' => $jobOrders[1]->id, // JOB-2026-0002
                'amount' => 2000.00,
                'payment_date' => Carbon::now()->subDays(4)->toDateString(),
                'payment_method' => 'promptpay',
                'payment_reference' => 'PP654987',
                'status' => 'verified',
                'received_by' => $users[1]->id,
                'note' => 'ชำระงวดแรก 2,000 บาท',
            ],
            [
                'job_order_id' => $jobOrders[3]->id, // JOB-2026-0004
                'amount' => 5000.00,
                'payment_date' => Carbon::now()->subDays(2)->toDateString(),
                'payment_method' => 'transfer',
                'payment_reference' => 'TXN11223344',
                'status' => 'verified',
                'received_by' => $users[1]->id,
                'note' => 'ชำระเต็มจำนวน รอเช็คเอกสารอื่น',
            ],
            [
                'job_order_id' => $jobOrders[1]->id, // JOB-2026-0002
                'amount' => 2500.00,
                'payment_date' => Carbon::now()->subDays(1)->toDateString(),
                'payment_method' => 'transfer',
                'payment_reference' => 'TXN998877',
                'status' => 'pending',
                'received_by' => null,
                'note' => 'ชำระงวดที่สอง รอตรวจสอบ',
            ],
            [
                'job_order_id' => $jobOrders[4]->id, // JOB-2026-0005
                'amount' => 4000.00,
                'payment_date' => Carbon::now()->subDays(5)->toDateString(),
                'payment_method' => 'cash',
                'payment_reference' => null,
                'status' => 'verified',
                'received_by' => $users[2]->id,
                'note' => 'ชำระมัดจำเงินสด',
            ]
        ];

        foreach ($paymentsData as $data) {
            JobOrderPayment::create($data);
        }
    }
}
