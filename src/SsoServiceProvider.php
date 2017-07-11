<?php

namespace Spinen\Discourse;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Routing\Registrar as Router;

/**
 * Class SsoServiceProvider
 *
 * @package Spinen\GarbageMan
 */
class SsoServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['router']->group(["middleware" => ["web", "auth"]], function (Router $router) {
            $router->get($this->app['config']->get('services.discourse.route'), [
                'uses' => 'Spinen\Discourse\Controllers\SsoController@login',
                'as'   => 'sso.login',
            ]);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
