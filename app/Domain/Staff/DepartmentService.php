<?php

namespace App\Domain\Staff;

use App\Domain\Common\Utils\ArrayUtil;
use App\Domain\Staff\Models\Department;
use App\Domain\Staff\Repositories\DepartmentRepository;
use App\Exceptions\BusinessException;

class DepartmentService
{
    /**
     * @var DepartmentRepository
     */
    protected $deptRepo;

    public function __construct()
    {
        $this->deptRepo = resolve(DepartmentRepository::class);
    }

    /**
     * 创建部门
     * @param int $parentId
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(int $parentId, string $name): Department
    {
        $parentDepartment = $this->deptRepo->find($parentId);
        if ($parentDepartment->parent_id === 0) {
            $path = (string)$parentDepartment->id;
        } else {
            $path = $parentDepartment->path.'-'.$parentDepartment->id;
        }

        return $this->deptRepo->create([
            'parent_id' => $parentId,
            'path' => $path,
            'name' => $name
        ]);
    }

    /**
     * 修改部门名称
     * @param int $id
     * @param string $name
     */
    public function updateName(int $id, string $name): void
    {
        $this->deptRepo->update($id, [
            'name' => $name
        ]);
    }

    /**
     * 删除部门
     * @param int $id
     * @throws BusinessException
     */
    public function delete(int $id): void
    {
        if ($id === 1) {
            throw new BusinessException('系统初始部门，不能删除');
        }
        if ($this->deptRepo->existsBy('parent_id', [$id])) {
            throw new BusinessException('存在子部门，不能删除');
        }
        if ($this->deptRepo->existsByStaffInDept($id)) {
            throw new BusinessException('部门内存在成员，不能删除');
        }

        $this->deptRepo->delete($id);
    }

    /**
     * 部门列表
     * @param bool $isTree
     * @param null $keyword
     * @return array
     */
    public function getList(bool $isTree, $keyword = null): array
    {
        if (is_null($keyword)) {
            $list = $isTree ? ArrayUtil::getTreeList($this->deptRepo->all()->toArray(), 0) : $this->deptRepo->all()->toArray();
        } else {
            $pathDeptIds = collect([]);
            $list = $this->deptRepo->allByKeywords($keyword)
                ->each(function ($department) use (&$pathDeptIds) {
                    if (!empty($department->path)) {
                        $pathDeptIds = $pathDeptIds->merge(explode('-', $department->path));
                    }
                });

            $pathDepartmentInfo = collect([]);
            if ($list->isNotEmpty() && $pathDeptIds->isNotEmpty()) {
                $pathDepartmentInfo = $this->deptRepo->getByManyId($pathDeptIds->unique()->all(), ['id', 'name'])->keyBy('id');
            }

            // 重构数据（附上部门路径）
            $list = $list->map(function ($department) use ($pathDepartmentInfo) {
                if ($department->path) {
                    $department->path_label = collect(explode('-', $department->path))->map(function ($item) use ($pathDepartmentInfo) {
                        return $pathDepartmentInfo[$item] ? $pathDepartmentInfo[$item]->name : '';
                    })->push($department->name);
                } else {
                    $department->path_label = [$department->name];
                }
                return $department;
            })->toArray();
        }

        return $list;
    }
}
