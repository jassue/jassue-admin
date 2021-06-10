<?php

namespace App\Domain\Admin\Repositories;

use App\Domain\Admin\Models\Admin;
use App\Domain\Common\BaseRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminRepository extends BaseRepository
{
    public function model(): string
    {
        return Admin::class;
    }

    /**
     * 根据角色获取管理员
     * @param array $roleIds
     * @param array $columns
     * @return Collection
     */
    public function getByRoleIds(array $roleIds, array $columns = ['*']): Collection
    {
        return DB::table('admin_has_roles')
            ->whereIn('role_id', $roleIds)
            ->get($columns);
    }

    /**
     * 获取当前用户
     * @return Admin
     */
    public function currentUser(): Admin
    {
        return Auth::user();
    }

    /**
     * 角色管理员是否存在
     * @param array $roleIds
     * @return bool
     */
    public function existsByRoleIds(array $roleIds): bool
    {
        return DB::table('admin_has_roles')->whereIn('role_id', $roleIds)->exists();
    }

    /**
     * 管理员是否存在
     * @param string $attribute
     * @param array $values
     * @return bool
     */
    public function existsBy(string $attribute, array $values): bool
    {
        return $this->query()->whereIn($attribute, $values)->exists();
    }

    /**
     * 绑定角色
     * @param Admin $admin
     * @param array $roleIds
     */
    public function bindRoles(Admin $admin, array $roleIds): void
    {
        $now = Carbon::now();
        $admin->roles()->attach(
            $roleIds,
            [
                'created_at' => $now,
                'updated_at' => $now
            ]
        );
    }

    /**
     * 同步角色
     * @param Admin $admin
     * @param array $roleIds
     */
    public function syncRoles(Admin $admin, array $roleIds): void
    {
        $now = Carbon::now();
        $admin->roles()->sync(
            collect($roleIds)->keyBy(function ($item) {
                return $item;
            })->map(function () use ($now) {
                return [
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            })->toArray()
        );
    }

    /**
     * 根据员工id获取数据
     * @param array $staffIds
     * @return Collection
     */
    public function getByStaffIds(array $staffIds): Collection
    {
        return $this->query()->whereIn('staff_id', $staffIds)->get();
    }

    /**
     * 删除管理员角色关系数据
     * @param array $ids
     * @return int
     */
    public function deleteRoleRelation(array $ids): int
    {
        return DB::table('admin_has_roles')->whereIn('admin_id', $ids)->delete();
    }

    /**
     * 创建或恢复管理员
     * @param array $conditions
     * @param array $data
     * @return Admin
     */
    public function createOrRestore(array $conditions, array $data): Admin
    {
        $admin = Admin::onlyTrashed()->updateOrCreate($conditions, $data);
        $admin->restore();
        return $admin;
    }
}
