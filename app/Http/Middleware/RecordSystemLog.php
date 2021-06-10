<?php

namespace App\Http\Middleware;

use App\Domain\SystemLog\SystemLogService;
use Closure;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class RecordSystemLog
{
    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $routeUri = Route::current()->uri;
        $logService = resolve(SystemLogService::class);
        if (!$logService->checkNeedRecord($routeUri)) {
            return $next($request);
        }
        $guard = Auth::guard('admin');
        $user = $guard->user();
        $response = $next($request);
        if ($response->exception ?? false) {
            return $response;
        }
        if (!$user && isset($response->original['data']['access_token'])) {
            $request->headers->set('Authorization', 'Bearer ' . $response->original['data']['access_token']);
            $user = $guard->user();
        }
        $user instanceof User && $logService->create($user, $routeUri, $request);
        return $response;
    }
}
