<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RoleAndPermissionSeeder::class);
        
        // Seed specializations
        $this->call(SpecializationSeeder::class);
        
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@digoscity.gov.ph',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        
        // Create staff user
        $staff = User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@digoscity.gov.ph',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');
        
        // Seed lawyers
        $this->call(LawyerSeeder::class);
        
        // Assign lawyer role to lawyer users
        $lawyerUsers = User::whereHas('lawyer')->get();
        foreach ($lawyerUsers as $user) {
            $user->assignRole('lawyer');
        }
    }
}
