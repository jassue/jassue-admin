<?php

namespace App\Domain\Admin;

use App\Domain\Admin\Config\PermissionEnum;
use App\Domain\Admin\Config\StatusEnum;
use App\Domain\Admin\Models\Admin;
use App\Domain\Admin\Models\AdminRole;
use App\Domain\Admin\Repositories\AdminRepository;
use App\Domain\Admin\Repositories\AdminRoleRepository;
use App\Domain\Staff\Repositories\StaffRepository;
use App\Exceptions\BusinessException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    /**
     * @var AdminRepository
     */
    protected $adminRepo;

    /**
     * @var AdminRoleRepository
     */
    protected $adminRoleRepo;

    /**
     * @var StaffRepository
     */
    protected $staffRepo;

    public function __construct()
    {
        $this->adminRepo = resolve(AdminRepository::class);
        $this->adminRoleRepo = resolve(AdminRoleRepository::class);
        $this->staffRepo = resolve(StaffRepository::class);
    }

    /**
     * 获取角色列表
     * @return Collection
     */
    public function getRoleList(): Collection
    {
        $list = $this->adminRoleRepo->all();

        $rolePermissions = $this->adminRoleRepo->getRolesPermissions($list->pluck('id')->toArray());

        return $list->map(function ($adminRole) use ($rolePermissions) {
            $adminRole->permission_ids = $adminRole->is_super
                ? PermissionEnum::getFlattenCollection()->pluck('id')->toArray()
                : $rolePermissions[$adminRole->id];
            return $adminRole;
        });
    }

    /**
     * 创建角色
     * @param string $name
     * @param array $permissionIds
     * @return AdminRole
     */
    public function createRole(string $name, array $permissionIds): AdminRole
    {
        return DB::transaction(function () use ($name, $permissionIds) {
            $role = $this->adminRoleRepo->create(['name'  => $name]);
            $this->adminRoleRepo->bindPermission($role->id, $permissionIds);
            $role->permission_ids = $permissionIds;
            return $role;
        });
    }

    /**
     * 获取树形权限列表
     * @return array
     */
    public function getPermissionTreeList(): array
    {
        return PermissionEnum::$permissionList;
    }

    /**
     * 更新角色
     * @param int $roleId
     * @param string $name
     * @param array $permissionIds
     * @return AdminRole
     * @throws BusinessException
     */
    public function updateRole(int $roleId, string $name, array $permissionIds): AdminRole
    {
        $this->checkRoleOperateAuth([$roleId]);

        return DB::transaction(function () use ($roleId, $name, $permissionIds) {
            $role = $this->adminRoleRepo->find($roleId);
            $this->adminRoleRepo->update($roleId, ['name' => $name]);
            $this->adminRoleRepo->syncPermission($roleId, $permissionIds);
            $this->forgetPermissionCacheByRoleIds([$roleId]);
            $role->permission_ids = $permissionIds;
            return $role;
        });
    }

    /**
     * 清除角色对应管理员权限缓存
     * @param array $ids
     */
    public function forgetPermissionCacheByRoleIds(array $ids): void
    {
        $this->adminRepo
            ->getByRoleIds($ids, ['admin_id'])
            ->each(function ($item) {
                $this->forgetPermissionCacheById($item->admin_id);
            });
    }

    /**
     * 清除管理员权限缓存
     * @param integer $id
     * @return void
     */
    public function forgetPermissionCacheById(int $id): void
    {
        Cache::forget(Admin::AUTH_CACHE_KEY_PRE.$id);
    }

    /**
     * 检查角色是否可被操作
     * @param array $operateRoleIds
     * @throws BusinessException
     */
    private function checkRoleOperateAuth(array $operateRoleIds): void
    {
        $this->adminRoleRepo
            ->getByManyId($operateRoleIds)
            ->each(function ($adminRole) {
                if ($adminRole->is_preset) {
                    throw new BusinessException('系统初始角色，不能操作');
                }
            });

        if ($this->adminRepo->currentUser()->roles->pluck('id')->intersect($operateRoleIds)->isNotEmpty()) {
            throw new BusinessException('该角色为自己所属角色，不能操作');
        }
    }

    /**
     * 删除角色
     * @param integer $roleId
     * @throws BusinessException
     */
    public function deleteRole(int $roleId): void
    {
        $this->checkRoleOperateAuth([$roleId]);

        if ($this->adminRepo->existsByRoleIds([$roleId])) {
            throw new BusinessException('角色已关联账号，无法删除，请先解除关联');
        }

        DB::transaction(function () use ($roleId) {
            $this->adminRoleRepo->delete($roleId);
            $this->adminRoleRepo->unbindPermission($roleId);
            $this->forgetPermissionCacheByRoleIds([$roleId]);
        });
    }

    /**
     * 获取管理员分页列表
     * @param int $page
     * @param int $size
     * @param string|null $keyword
     * @param int|null $roleId
     * @return array
     */
    public function getAdminList(int $page, int $size, string $keyword = null, int $roleId = null): array
    {
        $result = $this->adminRepo->paginate(function ($query) use ($keyword, $roleId) {
            return $query->with(['staff', 'roles'])
                ->when(!is_null($keyword), function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('mobile', 'like', "%{$keyword}%");
                })
                ->when(!is_null($roleId), function ($query) use ($roleId) {
                    $query->ofRoles([$roleId]);
                })
                ->orderBy('id', 'desc');
        }, $size);

        return [
            'data' => $result->items(),
            'total' => $result->total()
        ];
    }

    /**
     * 创建管理员
     * @param array $staffIds
     * @param array $roleIds
     * @param string $password
     * @param StatusEnum $status
     * @throws BusinessException
     */
    public function create(array $staffIds, array $roleIds, string $password, StatusEnum $status): void
    {
        if ($this->adminRepo->existsBy('staff_id', $staffIds)) {
            throw new BusinessException('员工已经是管理员，不能再次创建');
        }

        DB::transaction(function () use ($staffIds, $roleIds, $password, $status) {
            $this->staffRepo->getByManyId($staffIds)->each(function ($staff) use ($roleIds, $password, $status) {
                $admin = $this->adminRepo->createOrRestore([
                    'staff_id' => $staff->id
                ], [
                    'staff_id' => $staff->id,
                    'name' => $staff->name,
                    'mobile' => $staff->mobile,
                    'password' => $password,
                    'status' => $status->getValue()
                ]);
                $this->adminRepo->bindRoles($admin, $roleIds);
            });
        });
    }

    /**
     * 更新管理员
     * @param int $id
     * @param array $params
     * @throws BusinessException
     */
    public function update(int $id, array $params): void
    {
        $this->checkAdminOperateAuth([$id]);

        DB::transaction(function () use ($id, $params) {
            if (isset($params['password'])) {
                if (is_null($params['password'])) {
                    unset($params['password']);
                } else {
                    $params['password'] = bcrypt($params['password']);
                }
            }
            $admin = $this->adminRepo->find($id);
            $this->adminRepo->update($id, collect($params)->only(['password', 'status'])->toArray());
            $this->adminRepo->syncRoles($admin, $params['role_ids']);
            $this->forgetPermissionCacheById($id);
        });
    }

    /**
     * 账号密码登录
     * @param string $mobile
     * @param string $password
     * @return object|null|Admin
     * @throws BusinessException
     */
    public function loginByAccount(string $mobile, string $password): Admin
    {
        $admin = $this->adminRepo->findBy('mobile', $mobile);

        if (!$admin || !Hash::check($password, $admin->password)) {
            throw new BusinessException('账号密码错误');
        }

        if ($admin->status == StatusEnum::DISABLE) {
            throw new BusinessException('账号被禁用');
        }

        return $admin;
    }

    /**
     * 获取完整用户信息
     * @param Admin $admin
     * @return Admin
     */
    public function getInfoByAdmin(Admin $admin): Admin
    {
        $admin->permissions = $this->getAllPermissionByRoles($admin->roles);
        $admin->staff;
        return $admin;
    }

    /**
     * 获取角色的所有权限
     * @param Collection $roles
     * @return Collection
     */
    public function getAllPermissionByRoles(Collection $roles): Collection
    {
        if ($roles->contains('is_super', true)) {
            return PermissionEnum::getFlattenCollection();
        }

        $permissionIds = $this->adminRoleRepo->getPermissionIdsByRoles($roles->pluck('id')->toArray());

        return PermissionEnum::getByManyId($permissionIds)->values();
    }

    /**
     * 更新员工管理员信息
     * @param int $staffId
     * @param array $params
     * @return int
     */
    public function updateByStaffId(int $staffId, array $params): int
    {
        return $this->adminRepo->update($staffId, $params, 'staff_id');
    }

    /**
     * 修改密码
     * @param Admin $admin
     * @param string $oldPwd
     * @param string $newPwd
     * @throws BusinessException
     */
    public function setPassword(Admin $admin, string $oldPwd, string $newPwd): void
    {
        if (!Hash::check($oldPwd, $admin->password)) {
            throw new BusinessException('旧密码不正确');
        }
        $this->adminRepo->update($admin->id, [
            'password' => bcrypt($newPwd)
        ]);
    }

    /**
     * 检查管理员是否可被操作
     * @param array $adminIds
     * @throws BusinessException
     */
    private function checkAdminOperateAuth(array $adminIds): void
    {
        if (in_array(1, $adminIds)) {
            throw new BusinessException('系统初始管理员，不能操作');
        }
        if (in_array($this->adminRepo->currentUser()->id, $adminIds)) {
            throw new BusinessException('无法对自己进行操作');
        }
    }

    /**
     * 重置密码
     * @param array $ids
     * @param string $password
     * @throws BusinessException
     */
    public function resetPassword(array $ids, string $password): void
    {
        $this->checkAdminOperateAuth($ids);
        $this->adminRepo->update($ids, [
            'password' => bcrypt($password)
        ]);
    }

    /**
     * 删除管理员
     * @param array $ids
     * @throws BusinessException
     */
    public function delete(array $ids): void
    {
        $this->checkAdminOperateAuth($ids);

        DB::transaction(function () use ($ids) {
            $this->adminRepo->delete($ids);
            $this->adminRepo->deleteRoleRelation($ids);
        });
    }
}
