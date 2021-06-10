<?php

namespace App\Domain\Setting\Items;

use App\Domain\Setting\Config\StorageDriverTypeEnum;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage as StorageFacade;

class Storage extends BaseSetting
{
    protected function getKeyName(): string
    {
        return 'STORAGE';
    }

    protected function getDefaultValues(): array
    {
        return [
            'default' => 'public',
            'driver' => StorageDriverTypeEnum::getConstants()
        ];
    }

    public function getValidateRules(): array
    {
        return [
            [
                'data.default' => 'required|in:'.implode(',', StorageDriverTypeEnum::getNames()),
                'data.driver' => 'required',
                'data.driver.oss' => 'required|array',
                'data.driver.qiniu' => 'required|array'
            ],
            [
                'data.default.*' => '默认存储驱动配置错误',
                'data.driver.*' => '存储驱动配置错误',
                'data.driver.oss.*' => '阿里云对象存储驱动配置错误',
                'data.driver.qiniu.*' => '七牛云对象存储配置错误'
            ]
        ];
    }

    /**
     * @param StorageDriverTypeEnum $driverType
     * @return Filesystem
     */
    public function getStorageByDriverType(StorageDriverTypeEnum $driverType): Filesystem
    {
        $config = $driverType->buildFilesystemsConfig($this->getDetail()['driver'][$driverType->getName()]);

        !empty($config) && Config::set("filesystems.disks.{$driverType->getName()}", $config);

        return StorageFacade::disk($driverType->getName());
    }

    /**
     * @return Filesystem
     */
    public function getCurrentStorage(): Filesystem
    {
        return $this->getStorageByDriverType(StorageDriverTypeEnum::byName($this->getDetail()['default']));
    }

    /**
     * @param StorageDriverTypeEnum $driverType
     * @param string $path
     * @return string
     */
    public function getUrl(StorageDriverTypeEnum $driverType, string $path): string
    {
        return $driverType->getUrl(
            $this->getStorageByDriverType($driverType),
            $this->getDetail()['driver'][$driverType->getName()],
            $path
        );
    }
}
