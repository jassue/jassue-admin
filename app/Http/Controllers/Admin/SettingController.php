<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Setting\Config\SettingKeyEnum;
use App\Domain\Setting\SettingService;
use App\Exceptions\BusinessException;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    /**
     * @var SettingService
     */
    protected $settingService;

    public function __construct()
    {
        $this->settingService = resolve(SettingService::class);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function detail(Request $request)
    {
        $request->validate([
            'key' => 'required|string|in:'.implode(',', SettingKeyEnum::getNames())
        ], [
            'key.*' => '配置项参数错误'
        ]);

        return $this->success(
            $this->settingService->detail(SettingKeyEnum::byName($request->input('key')))
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws BusinessException
     */
    public function update(Request $request)
    {
        try {
            $settingKey = SettingKeyEnum::byName($request->input('key'));
        } catch(\Exception $e) {
            throw new BusinessException('未定义的配置项');
        }

        $request->validate(...$settingKey->getValidateRules());

        $this->settingService->update(
            SettingKeyEnum::byName($request->input('key')),
            $request->input('data')
        );

        return $this->success();
    }
}
