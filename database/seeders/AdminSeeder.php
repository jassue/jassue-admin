<?php

namespace Database\Seeders;

use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminRole;
use App\Domain\Staff\Models\Department;
use App\Domain\Staff\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            if (Department::count() == 0) {
                $now = Carbon::now();
                $staff = Staff::create([
                    'name' => 'admin',
                    'email' => '',
                    'mobile' => '18888888888',
                    'gender' => 0,
                ]);
                $department = Department::create([
                    'name' => '总部',
                ]);
                $department->staff()->attach($staff->id, [
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                $admin = Admin::create([
                    'staff_id' => $staff->id,
                    'name' => $staff->name,
                    'mobile' => $staff->mobile,
                    'password' => '123456'
                ]);
                $role = AdminRole::create([
                    'name' => '超级管理员',
                    'desc' => '由系统自动创建，具备所有功能权限。',
                    'is_super' => true,
                    'is_preset' => true
                ]);
                $admin->roles()->attach($role->id, [
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            }
        });
    }
}
