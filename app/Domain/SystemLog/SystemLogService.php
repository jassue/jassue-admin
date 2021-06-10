<?php

namespace App\Domain\SystemLog;

use App\Domain\Setting\Config\SettingKeyEnum;
use App\Domain\SystemLog\Repositories\SystemLogRepository;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SystemLogService
{
    protected static $config;

    /**
     * @var SystemLogRepository
     */
    protected $systemLogRepo;

    /**
     * SystemLogService constructor.
     */
    public function __construct()
    {
        $this->systemLogRepo = resolve(SystemLogRepository::class);

        if (!is_null(self::$config)) return;
        self::$config = collect([
            'admin/excel_upload' => function (Request $request) {
                return '上传excel文件，文件名称：'.$request->file('file')->getClientOriginalName();
            },
            'admin/image_upload' => function (Request $request) {
                return '上传图片，业务名称：'.$request->input('business');
            },
            'admin/auth/login' => function (Request $request) {
                return '登录系统';
            },
            'admin/auth/logout' => function (Request $request) {
                return '退出系统';
            },
            'admin/system/setting/update' => function (Request $request) {
                return '更新系统配置：'.SettingKeyEnum::byName($request->input('key'))->getValue()['desc'];
            },
            'admin/auth/update_profile' => function (Request $request) {
                return '更新个人信息';
            },
            'admin/auth/set_password' => function (Request $request) {
                return '修改个人密码';
            },
            'admin/staff/excel_import' => function (Request $request) {
                return '批量导入员工';
            },
            'admin/staff/create' => function (Request $request) {
                return '创建员工，名称：'.$request->input('name');
            },
            'admin/staff/set_dept' => function (Request $request) {
                return '调整员工部门，员工ID：'.implode(',', $request->input('ids'));
            },
            'admin/staff/update' => function (Request $request) {
                return '更新员工信息，ID：'.$request->input('id');
            },
            'admin/staff/delete' => function (Request $request) {
                return '删除员工，ID：'.implode(',', $request->input('ids'));
            },
            'admin/dept/create' => function (Request $request) {
                return '创建部门，名称：'.$request->input('name');
            },
            'admin/dept/update_name' => function (Request $request) {
                return '修改部门名称，ID：'.$request->input('id');
            },
            'admin/dept/delete' => function (Request $request) {
                return '删除部门，ID：'.$request->input('id');
            },
            'admin/role/create' => function (Request $request) {
                return '创建角色，名称：'.$request->input('name');
            },
            'admin/role/update' => function (Request $request) {
                return '更新角色，ID：'.$request->input('id');
            },
            'admin/role/delete' => function (Request $request) {
                return '删除角色，ID：'.$request->input('role_id');
            },
            'admin/admin/create' => function (Request $request) {
                return '创建管理员，员工ID：'.implode(',', $request->input('staff_ids'));
            },
            'admin/admin/update' => function (Request $request) {
                return '更新管理员，ID：'.$request->input('id');
            },
            'admin/admin/delete' => function (Request $request) {
                return '删除管理员，ID：'.implode(',', $request->input('ids'));
            },
            'admin/admin/reset_password' => function (Request $request) {
                return '重置管理员密码，ID：'.implode(',', $request->input('ids'));
            },
        ]);
    }

    /**
     * @param string $routeUri
     * @return bool
     */
    public function checkNeedRecord(string $routeUri): bool
    {
        return self::$config->has($routeUri);
    }

    /**
     * @param User $user
     * @param string $uri
     * @param Request $request
     */
    public function create(User $user, string $uri, Request $request): void
    {
        if (!$this->checkNeedRecord($uri)) return;

        $this->systemLogRepo->create([
            'operator_id' => $user->id,
            'operator_name' => $user->name,
            'content' => call_user_func(self::$config->get($uri), $request),
            'client_ip' => $this->getClientRealIp($request),
            'user_agent' => $request->userAgent()
        ]);
    }

    /**
     * @param Request $request
     * @return string|null
     */
    public function getClientRealIp(Request $request): ?string
    {
        return $request->header('x-real-ip') ??
            trim(explode(',', $request->header('x-forwarded-for'))[0]) ?? $request->getClientIp();
    }

    /**
     * @param int $page
     * @param int $size
     * @param null|string $startTime
     * @param null|string $endTime
     * @param null|string $adminName
     * @return array
     */
    public function getList(int $page, int $size, $startTime, $endTime, $adminName): array
    {
        $result = $this->systemLogRepo->paginate(function ($query) use ($startTime, $endTime, $adminName) {
            return $query->when(!is_null($startTime), function ($query) use ($startTime, $endTime) {
               $query->whereBetween('created_at', [
                   Carbon::parse($startTime)->startOfDay(),
                   Carbon::parse($endTime)->endOfDay()
               ]);
            })->when(!is_null($adminName), function ($query) use ($adminName) {
                $query->where('operator_name', 'like', "%{$adminName}%");
            })->orderBy('id', 'desc');
        }, $size);

        return [
            'data' => $result->items(),
            'total' => $result->total()
        ];
    }
}
