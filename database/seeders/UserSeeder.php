<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employer;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. สร้าง Roles เข้าไปในระบบก่อน (Spatie Permission)
        // แก้ไข: เปลี่ยนจาก updateOrCreate เป็น updateOrInsert สำหรับ \DB::table()
        \DB::table('roles')->updateOrInsert(
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]
        );
        \DB::table('roles')->updateOrInsert(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]
        );
        \DB::table('roles')->updateOrInsert(
            ['name' => 'staff', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]
        );
        \DB::table('roles')->updateOrInsert(
            ['name' => 'employer', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]
        );

        // 2. ข้อมูล Users ตัวอย่าง (ส่วนนี้ใช้ User Model จึงใช้ updateOrCreate ได้ปกติ)
        $usersData = [
            [
                'email' => 'admin@vcs.com',
                'name' => 'Admin VCS',
                'password' => Hash::make('admin'),
            ],
            [
                'email' => 'somchai.s@vcs.com',
                'name' => 'สมชาย สายตรวจ',
                'password' => Hash::make('12345678'),
            ],
            [
                'email' => 'somsri.m@vcs.com',
                'name' => 'สมศรี มั่งมี',
                'password' => Hash::make('12345678'),
            ],
            [
                'email' => 'somsri.employer@acme.test',
                'name' => 'Acme Corp (Employer)',
                'password' => Hash::make('secret'),
            ],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $users[] = User::updateOrCreate(['email' => $data['email']], $data);
        }

        // 3. ผูก Role ให้กับผู้ใช้แต่ละคน ผ่านตาราง Pivot ของ Spatie
        if (isset($users[0])) $users[0]->assignRole('super_admin'); 
        if (isset($users[1])) $users[1]->assignRole('admin');
        if (isset($users[2])) $users[2]->assignRole('staff');
        if (isset($users[3])) $users[3]->assignRole('employer');

        $employer = Employer::query()->orderBy('id')->first();

        if ($employer && isset($users[3])) {
            $users[3]->employers()->syncWithoutDetaching([
                $employer->id => ['role' => 'owner'],
            ]);
        }
    }
}
