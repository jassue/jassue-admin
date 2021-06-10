<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Staff\DepartmentService;
use App\Domain\Staff\Repositories\DepartmentRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends BaseController
{
    /**
     * @var DepartmentService
     */
    protected $deptService;

    public function __construct()
    {
        $this->deptService = resolve(DepartmentService::class);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer|exists:departments,id',
            'name' => [
                'required',
                'string',
                Rule::unique('departments')->where(function ($query) use ($request) {
                    $query->where('parent_id', $request->input('parent_id'));
                })
            ]
        ], [
            'parent_id.required' => '请选择上级部门',
            'parent_id.exists' => '上级部门不存在',
            'name.required' => '请输入部门名称',
            'name.unique' => '部门名称已存在'
        ]);

        return $this->success($this->deptService->create(
            $request->input('parent_id'),
            $request->input('name')
        ));
    }

    /**
     * @param Request $request
     * @param DepartmentRepository $deptRepo
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function updateName(Request $request, DepartmentRepository $deptRepo)
    {
        $request->validate([
            'id' => 'required|integer|exists:departments',
            'name' => [
                'required',
                'string',
                Rule::unique('departments')
                    ->ignore($request->input('id'))
                    ->where(function ($query) use ($request, $deptRepo) {
                        $department = $deptRepo->find($request->input('id'), ['parent_id']);
                        $query->where('parent_id', $department->parent_id);
                    })
            ]
        ], [
            'id.required' => '请选择部门',
            'id.exists' => '部门不存在',
            'name.required' => '请输入部门名称',
            'name.unique' => '部门名称已存在'
        ]);

        $this->deptService->updateName($request->input('id'), $request->input('name'));

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
            'id' => 'required|integer|exists:departments'
        ], [
            'id.required' => '请选择部门',
            'id.exists' => '部门不存在'
        ]);

        $this->deptService->delete($request->input('id'));

        return $this->success();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $request->validate([
            'keyword' => 'nullable|string',
            'is_tree' => 'required|boolean'
        ]);

        $data = $this->deptService->getList($request->input('is_tree'), $request->input('keyword'));

        return $this->success($data);
    }
}
