<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Verifications\VerificationRepository;
use App\Repositories\Verifications\VerificationInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(VerificationInterface::class, VerificationRepository::class);
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
