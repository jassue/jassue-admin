<?php

namespace App\Http\Middleware;

use App\Domain\SystemLog\SystemLogService;
use Closure;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        $response = $next($request);

        $this->process($request, $response);

        return $response;
    }

    private function process($request, $response)
    {
//        go(function () use ($request, $response) {
        try {
            $routeUri = Route::current()->uri;
            $logService = resolve(SystemLogService::class);
            if (!$logService->checkNeedRecord($routeUri)) {
                return;
            }
            if ($response->exception ?? false) {
                return;
            }
            $guard = Auth::guard('admin');
            $user = $guard->user();
            if (!$user && isset($response->original['data']['access_token'])) {
                $request->headers->set('Authorization', 'Bearer ' . $response->original['data']['access_token']);
                $user = $guard->user();
            }
            $user instanceof User && $logService->create($user, $routeUri, $request);
        } catch (\Exception $e) {
            Log::info('record log error:'.$e->getMessage());
        }
//        });
    }
}
