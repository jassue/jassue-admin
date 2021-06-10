<?php

namespace App\Http\Controllers\Common;

use App\Domain\Common\Config\UploadImageTypeEnum;
use App\Domain\Common\Helpers\ApiResponse;
use App\Domain\Common\Services\MediaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    use ApiResponse;

    /**
     * @var MediaService
     */
    protected $mediaService;

    public function __construct()
    {
        $this->mediaService = resolve(MediaService::class);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function excelUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ], [
            'file.required' => '请选择要上传的excel文件',
            'file.file' => '请选择要上传的excel文件',
            'file.mimetypes' => 'excel文件格式必须是xlsx'
        ]);
        return $this->success($this->mediaService->saveExcelFile($request->file('file')));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\BusinessException
     */
    public function imageUpload(Request $request)
    {
        $uploadImageType = UploadImageTypeEnum::byValue(intval($request->input('image_type')));
        $request->validate([
            'image_type' => 'required|integer|in:'.implode(',', UploadImageTypeEnum::getValues()),
            'image' => $uploadImageType->getValue() == UploadImageTypeEnum::BASE64
                ? 'required|string|min:1' : 'required|image',
            'business' => 'required|string|min:1'
        ], [
            'image_type.required' => '上传图片类型不能为空',
            'image_type.in' => '上传图片类型错误',
            'image.required' => '请选择要上传的图片',
            'image.min' => '请选择要上传的图片',
            'image.string' => '图片必须为base64编码',
            'image.image' => '上传图片格式不正确',
            'business.required' => '业务类型不能为空',
            'business.string' => '业务类型必须为字符串',
            'business.min' => '业务类型至少为一个字符'
        ]);

        $result = $this->mediaService->saveImage(
            $uploadImageType,
            $uploadImageType->getValue() == UploadImageTypeEnum::BASE64 ?
                $request->input('image') : $request->file('image'),
            $request->input('business')
        );

        return $this->success($result);
    }
}
