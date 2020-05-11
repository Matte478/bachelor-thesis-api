<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FillPermissionsAndRoles extends Migration
{
    protected $roles;
    protected $permissions;
    protected $guardName;

    /**
     * FillPermissionsAndRoles constructor.
     */
    public function __construct()
    {
        $this->guardName = 'api';

        $clientPermissions = collect([
            // employees
            'employee',
            'employee.index',
            'employee.register',
            'employee.show',
            'employee.edit',
            'employee.delete',

            // meals
            'meal.index',

            // agreements
            'agreement.index',
            'agreement.create',

            // restaurants
            'restaurant.index',

            // orders
            'order.index',
            'order.employee',
            'order.create',

            // type of employments
            'type-of-employment',
            'type-of-employment.index',
            'type-of-employment.create',
            'type-of-employment.show',
            'type-of-employment.edit',
            'type-of-employment.delete',
        ]);

        $employeePermissions = collect([
            // meals
            'meal.index',

            // orders
            'order.employee',
            'order.create',
        ]);

        $contractorPermissions = collect([
            // meals
            'meal.index',
            'meal.create',
            'meal.show',
            'meal.edit',
            'meal.delete',

            // agreements
            'agreement.index',
            'agreement.confirm',

            // orders
            'order.index',
        ]);

        $allPermissions = $clientPermissions
            ->merge($employeePermissions)
            ->merge($contractorPermissions);
        $allPermissions = $allPermissions->unique();

        $this->permissions = $allPermissions->map(function($permission) {
            return [
                'name' => $permission,
                'guard_name' => $this->guardName,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        })->toArray();

        $this->roles = [
            [
                'name' => 'Client',
                'guard_name' => $this->guardName,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'permissions' => $clientPermissions,
            ],

            [
                'name' => 'Employee',
                'guard_name' => $this->guardName,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'permissions' => $employeePermissions,
            ],

            [
                'name' => 'Contractor',
                'guard_name' => $this->guardName,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'permissions' => $contractorPermissions,
            ],
        ];
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function () {
            foreach ($this->permissions as $permission) {
                DB::table('permissions')->insert($permission);
            }

            foreach ($this->roles as $role) {
                $permissions = $role['permissions'];
                unset($role['permissions']);

                $roleId = DB::table('roles')->insertGetId($role);

                $permissionItems = DB::table('permissions')->whereIn('name', $permissions)->get();
                foreach ($permissionItems as $permissionItem) {
                    DB::table('role_has_permissions')->insert(['permission_id' => $permissionItem->id, 'role_id' => $roleId]);
                }
            }
        });
        app()['cache']->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function () {
            foreach (User::all() as $user) {
                DB::table('model_has_permissions')->where('model_id', '=', $user->id)->where('model_type', '=', 'App\Models\User')->delete();
                DB::table('model_has_roles')->where('model_id', '=', $user->id)->where('model_type', '=', 'App\Models\User')->delete();
            }

            foreach ($this->roles as $role) {
                if(!empty($roleItem = DB::table('roles')->where('name', '=', $role['name'])->first())) {
                    DB::table('roles')->where('id', '=', $roleItem->id)->delete();
                    DB::table('model_has_roles')->where('role_id', '=', $roleItem->id)->delete();
                }
            }

            foreach ($this->permissions as $permission) {
                if(!empty($permissionItem = DB::table('permissions')->where('name', '=', $permission['name'])->first())) {
                    DB::table('permissions')->where('id', '=', $permissionItem->id)->delete();
                    DB::table('model_has_permissions')->where('permission_id', '=', $permissionItem->id)->delete();
                }
            }
        });
        app()['cache']->forget(config('permission.cache.key'));
    }
}
