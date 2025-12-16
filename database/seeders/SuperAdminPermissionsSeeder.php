<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminPermissionsSeeder extends Seeder
{
    /**
     * Assign all permissions to Super Admin role.
     */
    public function run(): void
    {
        // Find the Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'web')->first();

        if (!$superAdminRole) {
            $this->command->error('Super Admin role not found. Please create it first.');
            return;
        }

        // Get all permissions for web guard
        $permissions = Permission::where('guard_name', 'web')->get();

        if ($permissions->isEmpty()) {
            $this->command->warn('No permissions found in the database.');
            return;
        }

        // Get permission names
        $permissionNames = $permissions->pluck('name')->toArray();

        // Use transaction to ensure atomicity
        DB::transaction(function () use ($superAdminRole, $permissionNames) {
            $superAdminRole->syncPermissions($permissionNames);
        });

        $this->command->info("Successfully assigned {$permissions->count()} permissions to Super Admin role.");
    }
}
