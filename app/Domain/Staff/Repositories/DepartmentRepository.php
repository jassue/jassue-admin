<?php

namespace App\Domain\Staff\Repositories;

use App\Domain\Common\BaseRepository;
use App\Domain\Staff\Models\Department;
use App\Domain\Staff\Models\DepartmentStaff;
use Illuminate\Support\Collection;

class DepartmentRepository extends BaseRepository
{
    public function model(): string
    {
        return Department::class;
    }

    /**
     * 根据父id及名称查找
     * @param int $parentId
     * @param string $name
     * @param array $columns
     * @return Department|null
     */
    public function getByPidAndName(int $parentId, string $name, array $columns = ['*']): ?Department
    {
        return Department::where('parent_id', $parentId)
            ->where('name', $name)
            ->first();
    }

    /**
     * 根据字段检查数据是否存在
     * @param string $attribute
     * @param array $values
     * @return bool
     */
    public function existsBy(string $attribute, array $values): bool
    {
        return Department::whereIn($attribute, $values)->exists();
    }

    /**
     * 检查部门是否存在成员
     * @param int $id
     * @return bool
     */
    public function existsByStaffInDept(int $id): bool
    {
        return DepartmentStaff::where('department_id', $id)->exists();
    }

    /**
     * 关键词搜索部门
     * @param string $keyword
     * @return Collection
     */
    public function allByKeywords(string $keyword): Collection
    {
        return Department::where('name', 'like', '%'.$keyword.'%')->get();
    }
}
