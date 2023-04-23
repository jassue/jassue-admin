<?php

namespace App\Domain\Setting\Config;

use App\Domain\Common\BaseEnum;
use Illuminate\Contracts\Filesystem\Filesystem;

class StorageDriverTypeEnum extends BaseEnum
{
    // 本地
    const PUBLIC = null;

    // 阿里云
    const OSS = [
        'access_key_id' => null,
        'access_key_secret' => null,
        'bucket' => null,
        'endpoint' => null,
        'is_ssl' => false
    ];

    // 七牛云
    const QINIU = [
        'access_key' => null,
        'bucket' => null,
        'domain' => null,
        'secret_key' => null,
        'is_ssl' => false
    ];

    /**
     * 获取驱动名称
     * @return string
     */
    public function getDiskName(): string
    {
        return strtolower($this->getName());
    }

    /**
     * @param $setting
     * @return array
     */
    public function buildFilesystemsConfig($setting): array
    {
        switch ($this->getDiskName()) {
            case 'oss':
                $config = [
                    'driver'        => $this->getDiskName(),
                    'access_id'     => $setting['access_key_id'],
                    'access_key'    => $setting['access_key_secret'],
                    'bucket'        => $setting['bucket'],
                    'endpoint'      => $setting['endpoint'],
                    'endpoint_internal' => '',
                    'cdnDomain'     => '',
                    'ssl'           => $setting['is_ssl'],
                    'isCName'       => false,
                    'debug'         => false
                ];
                break;
            case 'qiniu':
                $config = [
                    'driver'  => $this->getDiskName(),
                    'domains' => [
                        'default'   => $setting['domain'],
                        'https'     => $setting['domain'],
                        'custom'    => $setting['domain'],
                    ],
                    'access_key' => $setting['access_key'],
                    'secret_key' => $setting['secret_key'],
                    'bucket'     => $setting['bucket'],
                    'notify_url' => '',
                    'access'     => 'public',
                    'hotlink_prevention_key' => null
                ];
                break;
        }

        return $config ?? [];
    }

    /**
     * @param Filesystem $storage
     * @param array|null $setting
     * @param string $path
     * @return string
     */
    public function getUrl(Filesystem $storage, $setting, string $path)
    {
        switch ($this->getDiskName()) {
            case 'qiniu':
                $isSSL = $setting['is_ssl'];
                $url = $storage->url(
                    !$isSSL ? $path : [
                        'path' => $path,
                        'domainType' => 'https'
                    ]
                );
                break;
            default:
                $url = $storage->url($path);
                break;
        }

        return $url;
    }
}
