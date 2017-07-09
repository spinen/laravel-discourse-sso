<?php

namespace Spinen\Discourse;

use ArrayAccess as Application;
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

        $this->router_mock->shouldReceive('group')
                          ->once()
                          ->withArgs([["middleware" => "auth"], Mockery::any()]);

        // TODO: Make sure that when the closure is called as expected
        $this->assertNull($this->service_provider->boot());
    }
}
