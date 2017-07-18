<?php

namespace Spinen\Discourse;

use ArrayAccess as Application;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Routing\Registrar as Router;
use Illuminate\Support\ServiceProvider;
use Mockery;

class SsoServiceProviderTest extends TestCase
{
    /**
     * @var Mockery\Mock
     */
    protected $application_mock;

    /**
     * @var Mockery\Mock
     */
    protected $config_mock;

    /**
     * @var Mockery\Mock
     */
    protected $events_mock;

    /**
     * @var Mockery\Mock
     */
    protected $purge_command_mock;

    /**
     * @var Mockery\Mock
     */
    protected $router_mock;

    /**
     * @var ServiceProvider
     */
    protected $service_provider;

    public function setUp()
    {
        parent::setUp();

        $this->setUpMocks();

        $this->service_provider = new SsoServiceProvider($this->application_mock);
    }

    private function setUpMocks()
    {
        $this->application_mock = Mockery::mock(Application::class);
        $this->config_mock = Mockery::mock(Config::class);
        $this->router_mock = Mockery::mock(Router::class);
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(SsoServiceProvider::class, $this->service_provider);
    }

    /**
     * @test
     */
    public function it_boots_the_service()
    {
        $this->application_mock->shouldReceive('offsetGet')
                               ->once()
                               ->with('router')
                               ->andReturn($this->router_mock);

        $this->application_mock->shouldReceive('offsetGet')
                               ->once()
                               ->with('config')
                               ->andReturn($this->config_mock);

        $this->config_mock->shouldReceive('get')
                          ->with('services.discourse.route')
                          ->once()
                          ->andReturn('route');

        $this->router_mock->shouldReceive('get')
                          ->withArgs(
                              [
                                  'route',
                                  [
                                      'uses' => 'Spinen\Discourse\Controllers\SsoController@login',
                                      'as'   => 'sso.login',
                                  ],
                              ]
                          )
                          ->once()
                          ->andReturnNull();

        $route_closure = Mockery::on(
            function ($closure) {

                $closure($this->router_mock);

                return true;
            }
        );

        $this->router_mock->shouldReceive('group')
                          ->once()
                          ->withArgs([["middleware" => ["web", "auth"]], $route_closure]);

        $this->assertNull($this->service_provider->boot());
    }

    /**
     * @test
     */
    public function it_does_nothing_when_registering()
    {
        $this->service_provider->register();
    }
}
