<?php

namespace Spinen\Discourse\Listeners;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Mockery;
use Spinen\Discourse\TestCase;

/**
 * Class LogoutDiscourseUserTest
 *
 * @package Spinen\Discourse\Listeners
 */
class LogoutDiscourseUserTest extends TestCase
{
    /**
     * @var Mockery\Mock
     */
    protected $config_mock;

    /**
     * @var Client
     */
    protected $guzzle_mock;

    /**
     * @var Mockery\Mock
     */
    protected $request_mock;

    /**
     * @var Mockery\Mock
     */
    protected $user_mock;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpMocks();
    }

    private function setUpMocks()
    {
        $this->config_mock = Mockery::mock(Config::class);

        $this->guzzle_mock = Mockery::mock(Client::class);

        $this->request_mock = Mockery::mock(Request::class);

        $this->user_mock = Mockery::mock(User::class);
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $listener = new LogoutDiscourseUser($this->guzzle_mock);

        $this->assertInstanceOf(LogoutDiscourseUser::class, $listener);
    }
}

function abort($code)
{
    throw new Exception("Some error message", $code);
}

function redirect($path)
{
    return $path;
}
