<?php

namespace PrevailExcel\Coinremitter;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

/*
 * This file is part of the Laravel Coinremitter package.
 *
 * (c) Prevail Ejimadu <prevailexcellent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class CoinremitterServiceProvider extends ServiceProvider
{

    /*
    * Indicates if loading of the provider is deferred.
    *
    * @var bool
    */
    protected $defer = false;

    /**
    * Publishes all the config file this package needs to function
    */
    public function boot()
    {
        $config = realpath(__DIR__.'/../utils/config/coinremitter.php');

        $this->publishes([
            $config => config_path('coinremitter.php')
        ]);
        
        $this->mergeConfigFrom(
            __DIR__.'/../utils/config/coinremitter.php', 'coinremitter'
        );
        if (File::exists(__DIR__ . '/../utils/helpers/coinremitter.php')) {
            require __DIR__ . '/../utils/helpers/coinremitter.php';
        }


    }

    /**
    * Register the application services.
    */
    public function register()
    {
        $this->app->bind('laravel-coinremitter', function () {

            return new Coinremitter;

        });
    }

    /**
    * Get the services provided by the provider
    * @return array
    */
    public function provides()
    {
        return ['laravel-coinremitter'];
    }
}