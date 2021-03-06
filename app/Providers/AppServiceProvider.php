<?php

namespace App\Providers;

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
        \Laravel\Passport\Passport::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Schema::defaultStringLength(191);

        //debug模式下记录sql查询语句
        if(config('app.debug') === true){
            \DB::listen(function ($query) {
                \Log::channel('sql')->info(
                    '{sql query:'.json_encode($query->sql, JSON_UNESCAPED_UNICODE).'}---{bindings:'.json_encode($query->bindings, JSON_UNESCAPED_UNICODE).'}---{time:'.json_encode($query->time, JSON_UNESCAPED_UNICODE).'}');
            });
        }
    }
}
