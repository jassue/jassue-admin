<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Admin\Models\Admin;
use App\Domain\Common\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    use ApiResponse;

    const GUARD_NAME = 'admin';

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null|Admin
     */
    public function getCurrentAdmin()
    {
        return auth(self::GUARD_NAME)->user();
    }
}
