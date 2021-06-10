<?php

namespace App\Domain\Admin\Config;

use App\Domain\Common\BaseEnum;
use Illuminate\Support\Collection;

class PermissionEnum extends BaseEnum {
    const DASHBOARD = '仪表盘';

    const SYSTEM_CONFIG = '系统配置';
    const SYSTEM_LOG = '系统日志';

    const STAFF_DEPT_VIEW = '员工部门列表';
    const DEPT_CREATE = '添加部门';
    const DEPT_UPDATE = '编辑部门';
    const DEPT_DELETE = '删除部门';
    const STAFF_CREATE = '添加员工';
    const STAFF_UPDATE = '编辑员工';
    const STAFF_DELETE = '删除员工';

    const ADMIN_ROLE_VIEW = '管理员列表';
    const ADMIN_CREATE = '添加管理员';
    const ADMIN_UPDATE = '编辑管理员';
    const ADMIN_DELETE = '删除管理员';
    const ROLE_CREATE = '添加角色';
    const ROLE_UPDATE = '编辑角色';
    const ROLE_DELETE = '删除角色';

    public static $permissionList = [
        ['id' => 1, 'key' => 'DASHBOARD', 'name' => self::DASHBOARD],
        [
            'id' => 10,
            'key' => '',
            'name' => '系统管理',
            'children' => [
                ['id' => 11, 'key' => 'SYSTEM_CONFIG', 'name' => self::SYSTEM_CONFIG],
                ['id' => 12, 'key' => 'SYSTEM_LOG', 'name' => self::SYSTEM_LOG],
            ]
        ],
        [
            'id' => 100,
            'key' => '',
            'name' => '人员管理',
            'children' => [
                [
                    'id' => 101,
                    'key' => '',
                    'name' => '通讯录',
                    'children' => [
                        ['id' => 102, 'key' => 'STAFF_DEPT_VIEW', 'name' => self::STAFF_DEPT_VIEW],
                        ['id' => 103, 'key' => 'DEPT_CREATE', 'name' => self::DEPT_CREATE],
                        ['id' => 104, 'key' => 'DEPT_UPDATE', 'name' => self::DEPT_UPDATE],
                        ['id' => 105, 'key' => 'DEPT_DELETE', 'name' => self::DEPT_DELETE],
                        ['id' => 106, 'key' => 'STAFF_CREATE', 'name' => self::STAFF_CREATE],
                        ['id' => 107, 'key' => 'STAFF_UPDATE', 'name' => self::STAFF_UPDATE],
                        ['id' => 108, 'key' => 'STAFF_DELETE', 'name' => self::STAFF_DELETE],
                    ]
                ],
                [
                    'id' => 151,
                    'key' => '',
                    'name' => '管理员',
                    'children' => [
                        ['id' => 152, 'key' => 'ADMIN_ROLE_VIEW', 'name' => self::ADMIN_ROLE_VIEW],
                        ['id' => 153, 'key' => 'ADMIN_CREATE', 'name' => self::ADMIN_CREATE],
                        ['id' => 154, 'key' => 'ADMIN_UPDATE', 'name' => self::ADMIN_UPDATE],
                        ['id' => 155, 'key' => 'ADMIN_DELETE', 'name' => self::ADMIN_DELETE],
                        ['id' => 156, 'key' => 'ROLE_CREATE', 'name' => self::ROLE_CREATE],
                        ['id' => 157, 'key' => 'ROLE_UPDATE', 'name' => self::ROLE_UPDATE],
                        ['id' => 158, 'key' => 'ROLE_DELETE', 'name' => self::ROLE_DELETE]
                    ]
                ]
            ]
        ]
    ];

    /**
     * 权限列表(多维数组)展开为一维数组
     * @return Collection
     */
    public static function getFlattenCollection(): Collection
    {
        return collect(self::treeToList(self::$permissionList));
    }

    /**
     * @param array $data
     * @return array
     */
    public static function treeToList(array $data): array
    {
        $tree = [];
        foreach ($data as $item) {
            if (isset($item['children'])) {
                $childList = self::treeToList($item['children']);
                unset($item['children']);
                $tree = array_merge($tree, $childList);
            }
            !empty($item['key']) && $tree[] = $item;
        }
        return $tree;
    }

    /**
     * @param array $ids
     * @return Collection
     */
    public static function getByManyId(array $ids): Collection
    {
        return self::getFlattenCollection()->whereIn('id', $ids);
    }
}
