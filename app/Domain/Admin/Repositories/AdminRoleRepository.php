<?php

namespace App\Domain\Admin\Repositories;

use App\Domain\Admin\Models\AdminRole;
use App\Domain\Common\BaseRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminRoleRepository extends BaseRepository
{
    public function model(): string
    {
        return AdminRole::class;
    }

    /**
     * 获取角色权限id
     * @param array $roleIds
     * @return Collection
     */
    public function getRolesPermissions(array $roleIds): Collection
    {
        return DB::table('role_has_permissions')
            ->whereIn('role_id', $roleIds)
            ->get(['role_id', 'permission_id'])
            ->groupBy('role_id')
            ->map(function ($item) {
                return $item->pluck('permission_id')->toArray();
            });
    }

    /**
     * 绑定角色权限
     * @param int $roleId
     * @param array $permissionIds
     */
    public function bindPermission(int $roleId, array $permissionIds): void
    {
        $now = Carbon::now();
        DB::table('role_has_permissions')->insert(
            collect($permissionIds)->map(function ($permissionId) use ($roleId, $now) {
                $newItem['role_id'] = $roleId;
                $newItem['permission_id'] = $permissionId;
                $newItem['created_at'] = $now;
                $newItem['updated_at'] = $now;
                return $newItem;
            })->toArray()
        );
    }

    /**
     * 同步角色权限
     * @param int $roleId
     * @param array $permissionIds
     */
    public function syncPermission(int $roleId, array $permissionIds): void
    {
        $dbPermissionIds = DB::table('role_has_permissions')
            ->where('role_id', $roleId)
            ->pluck('permission_id');
        $needDeleteIds = $dbPermissionIds->diff(collect($permissionIds));
        $needInsertIds = collect($permissionIds)->diff($dbPermissionIds);

        if ($needDeleteIds->isNotEmpty()) {
            $this->unbindPermission($roleId, $needDeleteIds->toArray());
        }

        if ($needInsertIds->isNotEmpty()) {
            $this->bindPermission($roleId, $needInsertIds->toArray());
        }
    }

    /**
     * 移除角色权限
     * @param int $roleId
     * @param array $permissionIds 指定权限
     * @return int
     */
    public function unbindPermission(int $roleId, array $permissionIds = []): int
    {
        $query = DB::table('role_has_permissions')->where('role_id', $roleId);
        if (!empty($permissionIds)) {
            $query->whereIn('permission_id', $permissionIds);
        }
        return $query->delete();
    }

    /**
     * 获取角色拥有的权限id
     * @param array $roleIds
     * @return array
     */
    public function getPermissionIdsByRoles(array $roleIds): array
    {
        return DB::table('role_has_permissions')
            ->whereIn('role_id', $roleIds)
            ->pluck('permission_id')
            ->unique()
            ->toArray();
    }
}
