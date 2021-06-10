<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Admin\AdminService;
use App\Domain\Common\Utils\RegexUtil;
use Illuminate\Http\Request;

class AuthController extends BaseController
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
     * @throws \App\Exceptions\BusinessException
     */
    public function login(Request $request)
    {
        $request->validate([
            'mobile' => [
                'required',
                'regex:'.RegexUtil::PHONE
            ],
            'password' => 'required'
        ], [
            'mobile.required' => '请输入手机号码',
            'mobile.exists' => '账号不存在',
            'mobile.regex' => '手机号码格式不正确',
            'password' => '请输入密码'
        ]);

        $admin = $this->adminService->loginByAccount($request->input('mobile'), $request->input('password'));

        return $this->success($this->_buildTokenOutput(auth(self::GUARD_NAME)->login($admin)));
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function info()
    {
        return $this->success($this->adminService->getInfoByAdmin($this->getCurrentAdmin()));
    }

    /**
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function logout()
    {
        auth(self::GUARD_NAME)->logout();
        return $this->success();
    }

    /**
     * @param string $token
     * @return array
     */
    private function _buildTokenOutput(string $token)
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth(self::GUARD_NAME)->factory()->getTTL() * 60
        ];
    }
}
