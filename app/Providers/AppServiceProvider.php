<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\Drivers\Web\WebDriver;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BotMan::class, function ($app) {
            // Load the BotMan Web Driver
            DriverManager::loadDriver(WebDriver::class);

            // Get BotMan Config (if needed)
            $config = Config::get('botman.config', []);

            // Instantiate BotMan with Laravel Cache
            return BotManFactory::create($config, new LaravelCache());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        VerifyCsrfToken::except(['/botman']);
         Schema::defaultStringLength(191);
    }
}
