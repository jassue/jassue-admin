<?php

namespace App\Domain\Setting;

use App\Domain\Setting\Config\SettingKeyEnum;
use App\Domain\Setting\Models\Setting;
use App\Domain\Setting\Repositories\SettingRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    const CACHE_KEY_PRE = 'setting:';

    /**
     * @var SettingRepository
     */
    protected $settingRepo;

    public function __construct()
    {
        $this->settingRepo = resolve(SettingRepository::class);
    }

    /**
     * @param string $key
     * @return Setting|null|Model
     */
    public function find(string $key): ?Setting
    {
        return $this->settingRepo->findBy('key', $key);
    }

    /**
     * @param string $settingKeyName
     * @return string
     */
    public function getCacheKey(string $settingKeyName)
    {
        return self::CACHE_KEY_PRE.$settingKeyName;
    }

    /**
     * 配置详情
     * @param SettingKeyEnum $settingKeyEnum
     * @return array
     */
    public function detail(SettingKeyEnum $settingKeyEnum)
    {
        return $settingKeyEnum->getSetting()->getDetail();
    }

    /**
     * 保存设置
     * @param SettingKeyEnum $settingKeyEnum
     * @param array $params
     */
    public function update(SettingKeyEnum $settingKeyEnum, array $params): void
    {
        $this->settingRepo->updateOrCreate($settingKeyEnum, $params);

        Cache::forget($this->getCacheKey($settingKeyEnum->getName()));
    }
}
