<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffLoginCaseInsensitiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_login_even_when_email_case_differs(): void
    {
        Role::findOrCreate('staff', 'web');

        $user = User::factory()->create([
            'email' => 'staff@vcs.com',
            'password' => Hash::make('password'),
        ]);

        $user->assignRole('staff');

        $response = $this->post('/login', [
            'email' => 'Staff@vcs.com',
            'password' => 'password',
            'portal' => 'staff',
        ]);

        $response->assertRedirect(route('staff.portal.dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
