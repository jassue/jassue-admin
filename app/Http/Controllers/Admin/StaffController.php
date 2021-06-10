<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Common\Utils\RegexUtil;
use App\Domain\Staff\StaffService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends BaseController
{
    /**
     * @var StaffService
     */
    protected $staffService;

    public function __construct()
    {
        $this->staffService = resolve(StaffService::class);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:30',
            'email' => 'nullable|string|max:40|email',
            'mobile' => [
                'required',
                'string',
                'max:11',
                'regex:'.RegexUtil::PHONE,
                Rule::unique('staff')->whereNull('deleted_at')
            ],
            'gender' => 'required|in:1,2',
            'department_ids' => 'required|array|exists:departments,id',
            'position' => 'nullable|string',
            'job_number' => 'nullable|unique:staff'
        ], [
            'name.required' => '请输入姓名',
            'name.max' => '姓名最多可输入30个字符',
            'email.required' => '请输入邮箱',
            'email.max' => '邮箱最多可输入40个字符',
            'email.*' => '请输入正确的邮箱',
            'mobile.required' => '请输入手机号码',
            'mobile.unique' => '手机号码已存在',
            'mobile.*' => '手机号码格式错误',
            'gender.required' => '请选择性别',
            'gender.*' => '性别参数错误',
            'department_ids.required' => '请选择部门',
            'department_ids.array' => '部门参数错误',
            'department_ids.exists' => '部门不存在',
            'job_number.unique' => '工号已存在'
        ]);

        return $this->success($this->staffService->create($request->all(), $request->input('department_ids')));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\BusinessException
     */
    public function excelImport(Request $request)
    {
        $request->validate([
            'cache_key' => 'required|string'
        ]);

        return $this->success($this->staffService->excelImport($request->input('cache_key')));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:staff',
            'avatar_id' => 'nullable|integer',
            'name' => 'required|string|max:30',
            'gender' => 'required|in:1,2',
            'department_ids' => 'required|array|exists:departments,id',
            'position' => 'nullable|string',
            'job_number' => [
                'nullable',
                Rule::unique('staff')->ignore($request->input('id'))
            ]
        ], [
            'id.required' => '请选择员工',
            'id.exists' => '员工不存在',
            'avatar_id.integer' => '头像格式错误',
            'name.required' => '请输入姓名',
            'name.max' => '姓名最多可输入30个字符',
            'gender.required' => '请选择性别',
            'gender.*' => '性别参数错误',
            'department_ids.required' => '请选择部门',
            'department_ids.array' => '部门参数错误',
            'department_ids.exists' => '部门不存在',
            'job_number.unique' => '工号已存在'
        ]);

        return $this->success(
            $this->staffService->update(
                $request->input('id'),
                collect($request->all())->only(['name', 'avatar_id', 'gender', 'department_ids', 'position', 'job_number'])->toArray()
            )
        );
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\BusinessException
     */
    public function delete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array'
        ], [
            'ids.required' => '请选择员工',
            'ids.*' => 'ids参数错误'
        ]);

        $this->staffService->delete($request->input('ids'));

        return $this->success();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function setDept(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|exists:staff,id',
            'department_ids' => 'required|array|exists:departments,id'
        ], [
            'ids.required' => '请选择员工',
            'ids.exists' => '员工不存在',
            'ids.*' => 'ids参数错误',
            'department_ids.required' => '请选择部门',
            'department_ids.array' => '部门参数错误',
            'department_ids.exists' => '部门不存在',
        ]);

        $this->staffService->setDepartment($request->input('ids'), $request->input('department_ids'));

        return $this->success();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function updatePersonalInfo(Request $request)
    {
        $admin = $this->getCurrentAdmin();
        $rules = [
            'avatar_id' => 'nullable|integer',
            'name' => 'required|string|max:30',
            'email' => 'nullable|string|max:40|email',
            'mobile' => [
                'required',
                'string',
                'max:11',
                'regex:'.RegexUtil::PHONE,
                Rule::unique('staff')->ignore($admin->staff_id)
            ],
            'gender' => 'required|in:1,2',
        ];

        $request->validate($rules, [
            'avatar_id.integer' => '头像格式错误',
            'name.required' => '请输入姓名',
            'name.max' => '姓名最多可输入30个字符',
            'email.*' => '邮箱格式错误',
            'mobile.required' => '请输入手机号码',
            'mobile.unique' => '手机号码已存在',
            'mobile.*' => '手机号码格式错误',
            'gender.required' => '请选择性别',
            'gender.*' => '性别参数错误'
        ]);

        $result = $this->staffService->update($admin->staff_id, collect($request->all())->only(array_keys($rules))->all());

        return $this->success($result);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $request->validate([
            'department_id' => 'nullable|integer|exists:departments,id',
            'keyword' => 'nullable|string',
            'page' => 'required|integer|min:1',
            'size' => 'required|integer|min:1'
        ], [
            'department_id.required' => '请选择部门',
            'department_id.exists' => '部门不存在',
            'keyword.string' => '关键词格式错误',
            'page.required' => '请选择页码',
            'page.*' => '页码最小为1',
            'size.required' => '请选择页尺寸',
            'size.*' => '页尺寸最小为1'
        ]);

        $result = $this->staffService->getList(
            $request->input('page'),
            $request->input('size'),
            $request->input('department_id'),
            $request->input('keyword')
        );

        return $this->success($result);
    }
}
