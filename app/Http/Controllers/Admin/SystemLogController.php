<?php

namespace App\Http\Controllers\Admin;

use App\Domain\SystemLog\SystemLogService;
use Illuminate\Http\Request;

class SystemLogController extends BaseController
{
    /**
     * @var SystemLogService
     */
    protected $systemLogService;

    public function __construct()
    {
        $this->systemLogService = resolve(SystemLogService::class);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $request->validate([
            'start_time' => 'nullable|required_with:end_time|date_format:Y-m-d',
            'end_time' => 'nullable|required_with:start_time|date_format:Y-m-d|after_or_equal:start_time',
            'page' => 'required|integer|min:1',
            'size' => 'required|integer|min:1',
            'admin_name' => 'nullable|string'
        ], [
            'start_time.required_with' => '请选择开始时间',
            'start_time.date_format' => '开始时间格式错误，例：2021-01-01',
            'end_time.required_with' => '请选择结束时间',
            'end_time.date_format' => '结束时间格式错误，例：2021-01-31',
            'end_time.after_or_equal' => '结束时间必须大于或等于开始时间',
            'admin_name.*' => '管理员姓名格式错误',
            'page.required' => '请选择页码',
            'page.*' => '页码最小为1',
            'size.required' => '请选择页尺寸',
            'size.*' => '页尺寸最小为1'
        ]);

        $result = $this->systemLogService->getList(
            $request->input('page'),
            $request->input('size'),
            $request->input('start_time'),
            $request->input('end_time'),
            $request->input('admin_name')
        );

        return $this->success($result);
    }
}
