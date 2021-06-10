<?php

namespace App\Providers;

use App\Domain\Admin\AdminService;
use App\Domain\Admin\Repositories\AdminRepository;
use App\Domain\Admin\Repositories\AdminRoleRepository;
use App\Domain\Common\Services\MediaService;
use App\Domain\Setting\Repositories\SettingRepository;
use App\Domain\Setting\SettingService;
use App\Domain\Staff\DepartmentService;
use App\Domain\Staff\Repositories\DepartmentRepository;
use App\Domain\Staff\Repositories\StaffRepository;
use App\Domain\Staff\StaffService;
use App\Domain\SystemLog\Repositories\SystemLogRepository;
use App\Domain\SystemLog\SystemLogService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (env('APP_ENV') == 'local' && env('APP_DEBUG')) {
            \DB::listen(
                function ($sql) {
                    foreach ($sql->bindings as $i => $binding) {
                        if ($binding instanceof \DateTime) {
                            $sql->bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
                        } else {
                            if (is_string($binding)) {
                                $sql->bindings[$i] = "'$binding'";
                            }
                        }
                    }

                    // Insert bindings into query
                    $query = str_replace(array('%', '?'), array('%%', '%s'), $sql->sql);

                    $query = vsprintf($query, $sql->bindings);

                    // Save the query to file
                    $logFile = fopen(
                        storage_path('logs' . DIRECTORY_SEPARATOR . date('Y-m-d') . '_query.log'),
                        'a+'
                    );
                    fwrite($logFile, date('Y-m-d H:i:s') . ': ' . $query . PHP_EOL);
                    fclose($logFile);
                }
            );
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // repositories
        $this->app->singleton(AdminRepository::class, function () {
            return new AdminRepository();
        });
        $this->app->singleton(AdminRoleRepository::class, function () {
            return new AdminRoleRepository();
        });
        $this->app->singleton(SettingRepository::class, function () {
            return new SettingRepository();
        });
        $this->app->singleton(StaffRepository::class, function () {
            return new StaffRepository();
        });
        $this->app->singleton(DepartmentRepository::class, function () {
            return new DepartmentRepository();
        });
        $this->app->singleton(SystemLogRepository::class, function () {
            return new SystemLogRepository();
        });

        // services
        $this->app->singleton(AdminService::class, function () {
            return new AdminService();
        });
        $this->app->singleton(StaffService::class, function () {
            return new StaffService();
        });
        $this->app->singleton(DepartmentService::class, function () {
            return new DepartmentService();
        });
        $this->app->singleton(SettingService::class, function () {
            return new SettingService();
        });
        $this->app->singleton(SystemLogService::class, function () {
            return new SystemLogService();
        });
        $this->app->singleton(MediaService::class, function () {
            return new MediaService();
        });
    }
}
