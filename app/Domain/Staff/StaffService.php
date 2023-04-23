<?php

namespace App\Domain\Staff;

use App\Domain\Admin\AdminService;
use App\Domain\Admin\Repositories\AdminRepository;
use App\Domain\Common\Services\MediaService;
use App\Domain\Staff\Imports\StaffImport;
use App\Domain\Staff\Models\Staff;
use App\Domain\Staff\Repositories\DepartmentRepository;
use App\Domain\Staff\Repositories\StaffRepository;
use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class StaffService
{
    /**
     * @var StaffRepository
     */
    protected $staffRepo;

    /**
     * @var DepartmentRepository
     */
    protected $deptRepo;

    /**
     * @var AdminRepository
     */
    protected $adminRepo;

    /**
     * @var AdminService
     */
    protected $adminService;

    /**
     * @var MediaService
     */
    protected $mediaService;

    public function __construct()
    {
        $this->staffRepo = resolve(StaffRepository::class);
        $this->deptRepo = resolve(DepartmentRepository::class);
        $this->adminRepo = resolve(AdminRepository::class);
        $this->adminService = resolve(AdminService::class);
        $this->mediaService = resolve(MediaService::class);
    }

    /**
     * 创建员工
     * @param array $params
     * @param array $departmentIds
     * @return Staff
     */
    public function create(array $params, array $departmentIds = []): Staff
    {
        return DB::transaction(function () use ($params, $departmentIds) {
            $staff = $this->staffRepo->create($params);
            !empty($departmentIds) && $this->staffRepo->distributionDepartment($staff, $departmentIds);
            $staff->department_ids = $departmentIds;
            return $staff;
        });
    }

    /**
     * excel批量导入员工
     * @param string $cacheKey
     * @return array
     * @throws BusinessException
     */
    public function excelImport(string $cacheKey): array
    {
        $staffImport = new StaffImport;
        $data = Excel::toArray($staffImport, $path = $this->mediaService->getExcelFilePath($cacheKey), 'local')[0];
        $this->mediaService->deleteExcelFile($path);
        $count = count($data);
        if ($count > 200) {
            throw new BusinessException('单个文件最多导入200名成员');
        }

        $failureData = [];
        foreach ($data as $row) {
            $rowError = [];
            try {
                // 基础验证
                $staffImport->basicValidate($row);

                // 部门验证
                $departmentIdArr = [];
                foreach (explode(',', $row['departments']) as $departmentNameStr) {
                    $departmentNameArr = explode('/', $departmentNameStr);
                    $parentId = 0;
                    foreach ($departmentNameArr as $k => $departmentName) {
                        $department = $this->deptRepo->getByPidAndName($parentId, $departmentName);
                        if (!$department) {
                            throw new BusinessException($departmentNameStr.'不存在');
                        }
                        $parentId = $department->id;
                        if ($k == count($departmentNameArr)-1) {
                            $departmentIdArr[] = $department->id;
                        }
                    }
                }

                // 数据入库
                $this->create($row, array_unique($departmentIdArr));
            } catch (\Exception $e) {
                if ($e instanceof ValidationException) {
                    $rowError = [
                        'row_data' => $row,
                        'error' => collect($e->errors())->first()[0]
                    ];
                } else {
                    $rowError = [
                        'row_data' => $row,
                        'error' => $e->getMessage()
                    ];
                }
            }
            $rowError && $failureData[] = $rowError;
        }
        $failureCount = count($failureData);

        return [
            'total' => $count,
            'success_count' => isset($failureCount) ? $count - $failureCount : $count,
            'failure_count' => $failureCount ?? 0,
            'failure_data' => $failureData
        ];
    }

    /**
     * 编辑员工
     * @param int $id
     * @param array $params
     * @return Staff
     */
    public function update(int $id, array $params): Staff
    {
        return DB::transaction(function () use ($id, $params) {
            // 更新基本信息
            $staff = $this->staffRepo->find($id);
            $updateData = [];
            foreach ($params as $key => $value) {
                if (in_array($key, $staff->getFillable())) {
                    if ($key == 'avatar_id' && empty($value)) {
                        continue;
                    }
                    $updateData[$key] = is_null($value) ? '' : $value;
                    $staff->$key = $updateData[$key];
                }
            }
            $this->staffRepo->update($id, $updateData);
            !empty($params['department_ids']) && $this->staffRepo->syncDepartments($staff, $params['department_ids']);

            // 同步更新管理员信息
            $this->adminRepo->update($staff->id, [
                'name' => $params['name'],
                'mobile' => $params['mobile'] ?? $staff->mobile
            ], 'staff_id');

            return $staff;
        });
    }

    /**
     * 删除员工
     * @param array $ids
     * @throws BusinessException
     */
    public function delete(array $ids): void
    {
        if (in_array(1, $ids)) {
            throw new BusinessException('系统初始人员，不能删除');
        }

        if (in_array($this->adminRepo->currentUser()->staff_id, $ids)) {
            throw new BusinessException('不能删除自己');
        }

        DB::transaction(function () use ($ids) {
            $this->staffRepo->delete($ids);
            $this->staffRepo->removeDeptRelation($ids);
            $adminIds = $this->adminRepo->getByStaffIds($ids)->pluck('id');
            $this->adminService->delete($adminIds->toArray());
        });
    }

    /**
     * 调整部门
     * @param array $ids
     * @param array $departmentIds
     */
    public function setDepartment(array $ids, array $departmentIds): void
    {
        DB::transaction(function () use ($ids, $departmentIds) {
            $this->staffRepo->removeDeptRelation($ids);
            $this->staffRepo->insertDeptRelations($ids, $departmentIds);
        });
    }

    /**
     * 员工列表
     * @param int $page
     * @param int $size
     * @param int|null $departmentId
     * @param string|null $keyword
     * @return array
     */
    public function getList(int $page, int $size, int $departmentId = null, string $keyword = null): array
    {
        $list = $this->staffRepo->paginate(function ($query) use ($keyword, $departmentId) {
            return $query->with('departments')
                ->when(!is_null($keyword), function ($query) use ($keyword) {
                    $query->ofKeyword($keyword);
                })
                ->when(!is_null($departmentId), function ($query) use ($departmentId) {
                    $query->ofDepartments([$departmentId]);
                });
        }, $size);

        return [
            'data' => $list->items(),
            'total' => $list->total()
        ];
    }
}
