<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_roles_can_access_the_admin_filament_panel(): void
    {
        $panel = Filament::getPanel('admin');

        $admin = User::factory()->create(['role' => 'admin']);
        $manager = User::factory()->create(['role' => 'manager']);
        $staff = User::factory()->create(['role' => 'staff']);
        $hr = User::factory()->create(['role' => 'hr']);

        $this->assertTrue($admin->canAccessPanel($panel));
        $this->assertTrue($manager->canAccessPanel($panel));
        $this->assertFalse($staff->canAccessPanel($panel));
        $this->assertFalse($hr->canAccessPanel($panel));
    }

    public function test_only_staff_roles_can_access_the_staff_filament_panel(): void
    {
        $panel = Filament::getPanel('staff');

        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $accounting = User::factory()->create(['role' => 'accounting']);
        $hr = User::factory()->create(['role' => 'hr']);

        $this->assertFalse($admin->canAccessPanel($panel));
        $this->assertTrue($staff->canAccessPanel($panel));
        $this->assertTrue($accounting->canAccessPanel($panel));
        $this->assertFalse($hr->canAccessPanel($panel));
    }
}
