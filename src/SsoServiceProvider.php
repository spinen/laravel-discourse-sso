<?php

namespace Spinen\Discourse;

use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Support\ServiceProvider;

/**
 * Class SsoServiceProvider
 *
 * @package Spinen\Discourse
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
        $this->app['router']->group(
            ['middleware' => $this->app['config']->get('services.discourse.middleware', ['web', 'auth'])],
            function (Router $router) {
                $router->get(
                    $this->app['config']->get('services.discourse.route'),
                    [
                        'uses' => 'Spinen\Discourse\Controllers\SsoController@login',
                        'as'   => 'sso.login',
                    ]
                );
            }
        );
    }
}
