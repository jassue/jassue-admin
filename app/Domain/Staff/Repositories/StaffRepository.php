<?php

namespace App\Domain\Staff\Repositories;

use App\Domain\Common\BaseRepository;
use App\Domain\Staff\Models\DepartmentStaff;
use App\Domain\Staff\Models\Staff;
use Illuminate\Support\Carbon;

class StaffRepository extends BaseRepository
{
    public function model(): string
    {
        return Staff::class;
    }

    /**
     * 分配部门
     * @param Staff $staff
     * @param array $departmentIds
     */
    public function distributionDepartment(Staff $staff, array $departmentIds): void
    {
        $now = Carbon::now();
        $staff->departments()->attach($departmentIds, [
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    /**
     * 同步部门
     * @param Staff $staff
     * @param array $departmentIds
     */
    public function syncDepartments(Staff $staff, array $departmentIds): void
    {
        $now = Carbon::now();
        $staff->departments()->sync(
            collect($departmentIds)->flip()->map(function () use ($now) {
                return [
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            })->toArray()
        );
    }

    /**
     * 移除员工与部门关系
     * @param array $ids
     * @return int
     */
    public function removeDeptRelation(array $ids): int
    {
        return DepartmentStaff::whereIn('staff_id', $ids)->delete();
    }

    /**
     * 批量添加员工部门关系数据
     * @param array $staffIds
     * @param array $departmentIds
     * @return int
     */
    public function insertDeptRelations(array $staffIds, array $departmentIds): int
    {
        $now = Carbon::now();
        $data = collect($staffIds)->crossJoin(collect($departmentIds))
            ->map(function ($item) use ($now) {
                return [
                    'staff_id' => $item[0],
                    'department_id' => $item[1],
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            })
            ->all();
        return DepartmentStaff::insert($data);
    }
}
