<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Admin\AdminService;
use App\Domain\Admin\Config\StatusEnum;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends BaseController
{
    /**
     * @var AdminService
     */
    protected $adminService;

    public function __construct()
    {
        $this->adminService = resolve(AdminService::class);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function getRoleList(Request $request)
    {
        return $this->success($this->adminService->getRoleList());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function roleCreate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:20|unique:admin_roles',
            'permission_ids' => 'required|array'
        ], [
            'name.required' => '请输入角色名称',
            'name.max' => '角色名称最多20个字符',
            'name.unique' => '角色名称已存在',
            'permission_ids.required' => '请选择权限',
            'permission_ids.array' => '权限参数必须是数组'
        ]);

        return $this->success($this->adminService->createRole($request->input('name'), $request->input('permission_ids')));
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function getPermissionList()
    {
        return $this->success($this->adminService->getPermissionTreeList());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\BusinessException
     */
    public function roleUpdate(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:admin_roles',
            'name' => [
                'required',
                'string',
                'max:20',
                Rule::unique('admin_roles')->ignore($request->input('id'))
            ],
            'permission_ids' => 'required|array'
        ], [
            'id.required' => '请选择角色',
            'id.exists' => '角色不存在',
            'name.required' => '请输入角色名称',
            'name.max' => '角色名称最多20个字符',
            'name.unique' => '角色名称已存在',
            'permission_ids.required' => '请选择权限',
            'permission_ids.array' => '权限参数必须是数组'
        ]);

        $result = $this->adminService->updateRole(
            $request->input('id'),
            $request->input('name'),
            $request->input('permission_ids')
        );

        return $this->success($result);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\BusinessException
     */
    public function roleDelete(Request $request)
    {
        $request->validate([
            'role_id' => 'required|integer|exists:admin_roles,id'
        ], [
            'role_id.required' => '请选择角色',
            'role_id.exists' => '角色不存在'
        ]);

        $this->adminService->deleteRole($request->input('role_id'));

        return $this->success();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function adminList(Request $request)
    {
        $request->validate([
            'keyword' => 'nullable|string',
            'role_id' => 'nullable|integer|exists:admin_roles,id',
            'page' => 'required|integer|min:1',
            'size' => 'required|integer|min:1'
        ], [
            'keyword.string' => '关键词必须是字符串',
            'role_id.exists' => '角色不存在',
            'page.required' => '请选择页码',
            'page.*' => '页码最小为1',
            'size.required' => '请选择页尺寸',
            'size.*' => '页尺寸最小为1'
        ]);

        $result = $this->adminService->getAdminList(
            $request->input('page'),
            $request->input('size'),
            $request->input('keyword'),
            $request->input('role_id')
        );

        return $this->success($result);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\BusinessException
     */
    public function create(Request $request)
    {
        $request->validate([
            'staff_ids' => 'required|array|exists:staff,id',
            'role_ids' => 'required|array|exists:admin_roles,id',
            'password' => 'required|min:6|max:20',
            'status' => 'required|integer|in:'.implode(',', StatusEnum::getConstants())
        ], [
            'staff_ids.required' => '请选择员工',
            'staff_ids.exists' => '员工不存在',
            'role_ids.required' => '请选择角色',
            'role_ids.exists' => '角色不存在',
            'password.required' => '请输入密码',
            'password.*' => '密码格式为6-20个字符',
            'status.*' => '状态错误'
        ]);

        $this->adminService->create(
            $request->input('staff_ids'),
            $request->input('role_ids'),
            $request->input('password'),
            StatusEnum::byValue($request->input('status'))
        );

        return $this->success();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\BusinessException
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:admins',
            'role_ids' => 'required|array|exists:admin_roles,id',
            'password' => 'nullable|min:6|max:20',
            'status' => 'required|integer|in:'.implode(',', StatusEnum::getConstants())
        ], [
            'id.required' => '请选择管理员',
            'id.exists' => '管理员不存在',
            'role_ids.required' => '请选择角色',
            'role_ids.exists' => '角色不存在',
            'password.required' => '请输入密码',
            'password.*' => '密码格式为6-20个字符',
            'status.*' => '状态错误'
        ]);

        $this->adminService->update($request->input('id'), $request->all());

        return $this->success();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\BusinessException
     */
    public function delete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|exists:admins,id'
        ], [
            'ids.required' => '请选择管理员',
            'ids.exists' => '管理员不存在'
        ]);

        $this->adminService->delete($request->input('ids'));

        return $this->success();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\BusinessException
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|exists:admins,id',
            'password' => 'required|min:6|max:20'
        ], [
            'ids.required' => '请选择管理员',
            'ids.exists' => '管理员不存在',
            'password.required' => '请输入密码',
            'password.*' => '密码格式为6-20个字符',
        ]);

        $this->adminService->resetPassword($request->input('ids'), $request->input('password'));

        return $this->success();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\BusinessException
     */
    public function setPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:6|max:20',
            'confirm_password' => 'required|same:new_password'
        ], [
            'old_password.required' => '请输入旧密码',
            'new_password.required' => '请输入新密码',
            'new_password.*' => '密码格式为6-20个字符',
            'confirm_password.required' => '请输入确认密码',
            'confirm_password.same' => '确认密码与新密码不同'
        ]);

        $this->adminService->setPassword($this->getCurrentAdmin(), $request->input('old_password'), $request->input('new_password'));

        return $this->success();
    }
}
