<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-dashboard',
            'view-financials',
            'manage-vehicles',
            'manage-drivers',
            'dispatch-trips',
            'complete-trips',
            'cancel-trips',
        ];

        foreach($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $dispatcherRole = Role::firstOrCreate(['name' => 'dispatcher', 'guard_name' => 'web']);

        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $dispatcherRole->syncPermissions([
            'view-dashboard',
            'dispatch-trips',
            'complete-trips',
            'cancel-trips',
        ]);

        $adminUser = User::where('email', 'admin@fleet.test')->first();
        if ($adminUser) {
            $adminUser->assignRole($adminRole);
        }
    }
}
