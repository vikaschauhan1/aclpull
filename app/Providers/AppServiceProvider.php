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
        $repositories  = [
            'MoRequest',
            'RefreshUrl'
        ];

        foreach ($repositories as $name) {
            $this->app->bind(
                'App\Repositories\\'.$name.'\\'.$name.'RepositoryInterface',
                'App\Repositories\\'.$name.'\\'.$name.'Repository'
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
        //
    }
}
