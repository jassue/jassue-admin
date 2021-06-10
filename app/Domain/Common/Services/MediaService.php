<?php

namespace App\Domain\Common\Services;

use App\Domain\Common\Config\UploadImageTypeEnum;
use App\Domain\Setting\Config\SettingKeyEnum;
use App\Domain\Setting\Config\StorageDriverTypeEnum;
use App\Domain\Setting\Items\Storage as StorageSetting;
use App\Exceptions\BusinessException;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class MediaService
{
    const EXCEL_CACHE_KEY_PRE = 'tmp_excel:';
    const MEDIA_CACHE_KEY_PRE = 'media:';

    /**
     * @var StorageSetting
     */
    protected $storageSetting;

    public function __construct()
    {
        $this->storageSetting = SettingKeyEnum::STORAGE()->getSetting();
    }

    /**
     * @param $business
     * @param $withDate
     * @return string
     */
    public function makeFaceDir($business, $withDate = false)
    {
        return config('app.env').'/'.$business.($withDate ? '/'.Carbon::today()->format('Y-m-d') : '');
    }

    /**
     * 保存excel文件到本地
     * @param UploadedFile $file
     * @param string $business
     * @return array
     */
    public function saveExcelFile(UploadedFile $file, string $business = 'tmp_excel')
    {
        $path = Storage::disk('local')->putFile(self::makeFaceDir($business), $file);
        $cacheKey = Uuid::uuid4()->toString();
        Cache::put(self::EXCEL_CACHE_KEY_PRE.$cacheKey, $path, 24 * 60 * 60);
        return [
            'cache_key' => $cacheKey,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize()
        ];
    }

    /**
     * 获取excel文件路径
     * @param string $cacheKey
     * @return mixed
     * @throws BusinessException
     */
    public function getExcelFilePath(string $cacheKey)
    {
        if (!$path = Cache::pull(self::EXCEL_CACHE_KEY_PRE.$cacheKey)) {
            throw new BusinessException('excel文件不存在');
        }
        return $path;
    }

    /**
     * 删除本地excel文件
     * @param string $path
     */
    public function deleteExcelFile(string $path)
    {
        Storage::disk('local')->delete($path);
    }

    /**
     * 保存图片
     * @param UploadImageTypeEnum $uploadImageTypeEnum
     * @param string|UploadedFile $image
     * @param string $business
     * @return array
     * @throws BusinessException
     */
    public function saveImage(UploadImageTypeEnum $uploadImageTypeEnum, $image, string $business = 'default')
    {
        try {
            $storage = $this->storageSetting->getCurrentStorage();
            switch ($uploadImageTypeEnum->getValue()) {
                case UploadImageTypeEnum::BASE64:
                    $base64String = explode(',', $image);
                    if(count($base64String) != 2)
                        throw new BusinessException('base64编码错误');
                    if (!preg_match('/^(data:\s*image\/(\w+);base64,)/', $image, $result))
                        throw new BusinessException('非法文件上传');
                    if(!in_array($result[2], ["png", "jpg","gif","jpeg"]))
                        throw new BusinessException('请上传格式为png、jpg、gif、jpeg的图片');
                    $path = self::makeFaceDir($business).'/'.Str::uuid().'.'.$result[2];
                    if (!$storage->put($path, base64_decode($base64String[1]))) {
                        throw new BusinessException('上传失败');
                    }
                    break;

                case UploadImageTypeEnum::RAW:
                    $path = $storage->putFile(self::makeFaceDir($business), $image);
                    if ($path === false) {
                        throw new BusinessException('上传失败');
                    }
                    break;

                default:
                    throw new BusinessException('非法上传');
            }
        } catch (\Exception $e) {
            throw new BusinessException('图片上传失败，请检查系统配置');
        }

        return [
            'id' => Media::create([
                'driver_type' => $this->storageSetting->getDetail()['default'],
                'src_type' => 1,
                'src' => $path
            ])->id,
            'path' => $path,
            'url' =>  $this->getUrlByPath(StorageDriverTypeEnum::byName($this->storageSetting->getDetail()['default']), $path)
        ];
    }

    /**
     * 根据id获取资源链接
     * @param $mediaId
     * @return string
     * @throws BusinessException
     */
    public function getUrlById($mediaId): string
    {
        if (empty($mediaId)) return '';

        $cacheKey = self::MEDIA_CACHE_KEY_PRE . $mediaId;
        if (Cache::has($cacheKey)) {
            $mediaUrl = Cache::get($cacheKey);
        } else {
            $media = Media::find($mediaId);
            if (!empty($media)) {
                $mediaUrl = $this->getUrlByMedia($media);
                Cache::put($cacheKey, $mediaUrl, 3 * 24 * 3600);
            }
        }

        return $mediaUrl ?? '';
    }

    /**
     * @param Media $media
     * @return mixed|string
     * @throws BusinessException
     */
    public function getUrlByMedia(Media $media): string
    {
        switch ($media->src_type) {
            case 1:
                $url = $this->getUrlByPath(StorageDriverTypeEnum::byName($media->driver_type), $media->src);
                break;
            case 2:
                $url = $media->src;
                break;
            default:
                throw new BusinessException('media src_type error');
        }

        return $url;
    }

    /**
     * 获取文件url
     * @param StorageDriverTypeEnum $driverType
     * @param string $path
     * @return string
     */
    public function getUrlByPath(StorageDriverTypeEnum $driverType, string $path): string
    {
        try {
            return $this->storageSetting->getUrl($driverType, $path);
        } catch (\Throwable $e) {
            Log::error('获取图片url失败', [
                'driver_type' => $driverType->getValue(),
                'path' => $path,
                'err_msg' => $e->getMessage()
            ]);
            return '';
        }
    }
}
