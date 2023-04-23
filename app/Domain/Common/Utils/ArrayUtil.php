<?php

namespace App\Domain\Common\Utils;

class ArrayUtil
{
    /**
     * 一维数组转树形结构
     * @param array $data
     * @param int $pid
     * @param string $pName
     * @param string $pidName
     * @return array
     */
    public static function getTreeList(array $data, int $pid, string $pName = 'parent_id', string $pidName = 'id')
    {
        $tree = [];
        foreach ($data as $k => $v) {
            if ($v[$pName] == $pid) {
                $v['children'] = self::getTreeList($data, $v[$pidName], $pName, $pidName);
                if (empty($v['children'])) {
                    unset($v['children']);
                }
                $tree[] = $v;
                unset($data[$k]);
            }
        }
        return $tree;
    }
}
