<?php

namespace Spinen\Discourse;

use ArrayAccess as Application;
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
    }

    /**
     * @test
     * @group unit
     */
    public function it_can_be_constructed()
    {
        $this->assertInstanceOf(SsoServiceProvider::class, $this->service_provider);
    }

    /**
     * @test
     * @group unit
     */
    public function it_boots_the_service()
    {
        $this->assertNull($this->service_provider->boot());
    }
}
