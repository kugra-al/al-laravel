<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'access admin']);
        Permission::create(['name' => 'access items']);

        // create roles and assign created permissions


        // this can be done as separate statements
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo('access admin');
        $role->givePermissionTo('access items');

        $role = Role::create(['name' => 'creator']);
        $role->givePermissionTo('access items');

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());

        // Assign admin/super-admin roles for DEFAULT_ADMIN_USER
        $userEmail = env('DEFAULT_ADMIN_USER_EMAIL');
        if ($userEmail) {
            $user = User::where('email',$userEmail)->first();
            if (!$user) {
                $user = new User;
                $user->email = $userEmail;
                $user->name = env('DEFAULT_ADMIN_USER_NAME');
                $user->password = \Hash::make(env('DEFAULT_ADMIN_USER_PASS'));
                $user->save();
            }
            $user->assignRole('super-admin','admin');
        }

    }
}
