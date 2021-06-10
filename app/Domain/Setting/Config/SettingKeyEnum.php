<?php

namespace App\Domain\Setting\Config;

use App\Domain\Common\BaseEnum;
use App\Domain\Setting\Items\BaseSetting;
use App\Domain\Setting\Items\Storage;

class SettingKeyEnum extends BaseEnum
{
    const STORAGE = [
        'desc' => '上传配置',
        'setting' => Storage::class
    ];

    private $setting;

    /**
     * @return BaseSetting
     */
    public function getSetting(): BaseSetting
    {
        if (empty($this->setting)) {
            $settingClass = $this->getValue()['setting'];
            $this->setting = new $settingClass();
        }
        return $this->setting;
    }

    /**
     * @return array
     */
    public function getValidateRules()
    {
        $settingRules = $this->getSetting()->getValidateRules();

        return [
            array_merge(
                [
                    'key' => 'required|in:'.implode(',', self::getNames()),
                    'data' => 'required|array'
                ],
                $settingRules[0]
            ),
            array_merge(
                [
                    'key.*' => '配置项参数错误',
                    'data.*' => '配置错误'
                ],
                $settingRules[1]
            )
        ];
    }
}
