<?php

namespace App\Domain\Setting\Items;

use App\Domain\Setting\SettingService;
use Illuminate\Support\Facades\Cache;

abstract class BaseSetting
{
    /**
     * @var SettingService
     */
    protected $settingService;

    /**
     * BaseSetting constructor.
     */
    public function __construct()
    {
        $this->settingService = resolve(SettingService::class);
    }

    /**
     * 获取设置项Key
     * @return string
     */
    abstract protected function getKeyName(): string;

    /**
     * 获取默认配置
     * @return array
     */
    abstract protected function getDefaultValues(): array;

    /**
     * 获取验证规则
     * @return array
     */
    abstract public function getValidateRules(): array;

    /**
     * 获取配置项值
     * @return array
     */
    public function getDetail()
    {
        return Cache::rememberForever($this->settingService->getCacheKey($this->getKeyName()), function () {
            return $this->settingService->find($this->getKeyName());
        })->values ?? $this->getDefaultValues();
    }
}
