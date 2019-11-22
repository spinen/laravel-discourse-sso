<?php

namespace Spinen\Discourse\Listeners;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Config\Repository;
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
     * @var Repository
     */
    protected $config_mock;

    /**
     * @var Client
     */
    protected $guzzle_mock;

    /**
     * @var LogoutDiscourseUser
     */
    protected $listener;

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

        $this->listener = new LogoutDiscourseUser($this->guzzle_mock, $this->config_mock);
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
        $this->assertInstanceOf(LogoutDiscourseUser::class, $this->listener);
    }

    /**
     * @test
     */
    public function it_logs_out_the_discourse_user_when_triggered()
    {
        $this->user_mock->id = 1;

        $configs = [
            'base_uri' => 'http://discourse.example.com',
            'headers'  => [
                'Api-Key'      => 'testkey',
                'Api-Username' => 'testuser',
            ],
        ];

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('getBody')
                 ->once()
                 ->andReturn(json_encode(['user' => $this->user_mock]));
        $response->shouldReceive('getStatusCode')
                 ->once()
                 ->andReturn(200);

        $this->config_mock->shouldReceive('get')
                          ->with('services.discourse.url')
                          ->once()
                          ->andReturn($configs['base_uri']);

        $this->config_mock->shouldReceive('get')
                          ->with('services.discourse.api.key')
                          ->once()
                          ->andReturn($configs['headers']['Api-Key']);

        $this->config_mock->shouldReceive('get')
                          ->with('services.discourse.api.user')
                          ->once()
                          ->andReturn($configs['headers']['Api-Username']);

        $this->guzzle_mock->shouldReceive('get')
                          ->with('users/by-external/1.json', $configs)
                          ->once()
                          ->andReturn($response);

        $this->guzzle_mock->shouldReceive('post')
                          ->with('admin/users/1/log_out')
                          ->andReturn($response);

        $event = Mockery::mock(Logout::class);
        $event->user = $this->user_mock;

        $this->listener->handle($event);
    }

    /**
     * @test
     */
    public function if_it_receives_no_user_it_does_nothing_and_returns()
    {
        $this->markTestIncomplete();
    }
}
