<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Appointment permissions
            'view appointments',
            'create appointments',
            'edit appointments',
            'delete appointments',
            'confirm appointments',
            'cancel appointments',
            
            // Lawyer permissions
            'view lawyers',
            'create lawyers',
            'edit lawyers',
            'delete lawyers',
            'approve lawyers',
            
            // Client record permissions
            'view client records',
            'create client records',
            'edit client records',
            'delete client records',
            
            // Document permissions
            'view documents',
            'upload documents',
            'verify documents',
            'delete documents',
            
            // Report permissions
            'view reports',
            'export reports',
            
            // Settings permissions
            'manage settings',
            'manage users',
            'manage roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        
        // Admin - Full access
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Lawyer - Limited access
        $lawyerRole = Role::firstOrCreate(['name' => 'lawyer']);
        $lawyerRole->givePermissionTo([
            'view appointments',
            'edit appointments',
            'confirm appointments',
            'cancel appointments',
            'view client records',
            'edit client records',
            'view documents',
            'verify documents',
        ]);

        // Staff - Queue and appointment management
        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'view appointments',
            'create appointments',
            'edit appointments',
            'confirm appointments',
            'cancel appointments',
            'view client records',
            'create client records',
            'edit client records',
            'view documents',
            'upload documents',
        ]);
    }
}
