<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

// Create Permissions
         $permissions = [
             // User Permissions
             'view users',
             'create users',
             'edit users',
             'delete users',

// Pizza Permissions
              'view pizzas',
              'create pizzas',
              'edit pizzas',
              'delete pizzas',

             // Category Permissions
             'view categories',
             'create categories',
             'edit categories',
             'delete categories',

             // Order Permissions
             'view orders',
             'create orders',
             'edit orders',
             'delete orders',

             // Review Permissions
             'view reviews',
             'delete reviews',

             // Role/Permission Management
             'manage roles',
             'manage permissions',
         ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $vendor = Role::firstOrCreate(['name' => 'vendor']);
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $moderator = Role::firstOrCreate(['name' => 'moderator']);

        // Assign All Permissions to Admin
        $admin->syncPermissions($permissions);

        // Assign Vendor Permissions
        $vendor->syncPermissions([
            'view pizzas',
            'create pizzas',
            'edit pizzas',
            'view orders',
            'view reviews',
        ]);

        // Assign Moderator Permissions
        $moderator->syncPermissions([
            'view users',
            'view pizzas',
            'view orders',
            'view reviews',
            'delete reviews',
        ]);

        // Customer has no special permissions
        $customer->syncPermissions([]);

        $this->command->info('Roles and Permissions seeded successfully!');
    }
}
