<?php

namespace App\Domain\Setting\Repositories;

use App\Domain\Common\BaseRepository;
use App\Domain\Setting\Config\SettingKeyEnum;
use App\Domain\Setting\Models\Setting;

class SettingRepository extends BaseRepository
{
    public function model(): string
    {
        return Setting::class;
    }

    /**
     * 更新/创建配置项
     * @param SettingKeyEnum $settingKeyEnum
     * @param array $params
     */
    public function updateOrCreate(SettingKeyEnum $settingKeyEnum, array $params): void
    {
        $this->query()->updateOrCreate([
                'key' => $settingKeyEnum->getName()
            ], [
                'desc' => $settingKeyEnum->getValue()['desc'],
                'values' => $params
            ]);
    }
}
