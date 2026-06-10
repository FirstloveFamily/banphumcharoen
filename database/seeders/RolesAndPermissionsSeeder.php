<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // permissions
        $perms = [
            'view workers',
            'edit workers',
            'delete workers',
            'export reports',
            'view workers-expiry-dashboard',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // roles
        $super = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $staff = Role::firstOrCreate(['name' => 'staff']);
        $employer = Role::firstOrCreate(['name' => 'employer']);

        // give permissions
        $super->givePermissionTo(Permission::all());
        $admin->givePermissionTo(['view workers', 'export reports', 'view workers-expiry-dashboard']);
        $staff->givePermissionTo(['view workers', 'view workers-expiry-dashboard']);

        // assign admin@admin.com to admin role if exists
        $adminUser = User::where('email', 'admin@admin.com')->first();
        if ($adminUser) {
            $adminUser->assignRole($admin->name);
        }
    }
}
