<?php

namespace Spinen\Discourse\Listeners;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Mockery;
use Spinen\Discourse\TestCase;
use Symfony\Component\HttpKernel\Log\Logger;

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
     * @var Mockery\Mock
     */
    protected $event_mock;

    /**
     * @var Mockery\Mock
     */
    protected $guzzle_mock;

    /**
     * @var LogoutDiscourseUser
     */
    protected $listener;

    /**
     * @var Mockery\Mock
     */
    protected $logger_mock;

    /**
     * @var Mockery\Mock
     */
    protected $request_mock;

    /**
     * @var Mockery\Mock
     */
    protected $response_mock;

    /**
     * @var Mockery\Mock
     */
    protected $user_mock;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpMocks();

        $this->listener = new LogoutDiscourseUser($this->guzzle_mock, $this->config_mock, $this->logger_mock);
    }

    private function setUpMocks()
    {
        $this->config_mock = Mockery::mock(Config::class);

        $this->event_mock = Mockery::mock(Logout::class);

        $this->guzzle_mock = Mockery::mock(Client::class);

        $this->logger_mock = Mockery::mock(Logger::class);

        $this->request_mock = Mockery::mock(Request::class);

        $this->response_mock = Mockery::mock(Response::class);

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
        $this->event_mock->user = $this->user_mock;

        $configs = [
            'base_uri' => 'http://discourse.example.com',
            'headers'  => [
                'Api-Key'      => 'testkey',
                'Api-Username' => 'testuser',
            ],
        ];

        $this->response_mock->shouldReceive('getBody')
                 ->once()
                 ->andReturn(json_encode(['user' => $this->user_mock]));

        $this->response_mock->shouldReceive('getStatusCode')
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
                          ->andReturn($this->response_mock);

        $this->guzzle_mock->shouldReceive('post')
                          ->with('admin/users/1/log_out')
                          ->andReturn($this->response_mock);

        $this->listener->handle($this->event_mock);
    }

    /**
     * @test
     */
    public function if_it_receives_no_user_it_does_nothing_and_returns()
    {
        $this->response_mock->shouldNotReceive('getBody');
        $this->config_mock->shouldNotReceive('get');
        $this->guzzle_mock->shouldNotReceive('get');
        $this->guzzle_mock->shouldNotReceive('post');

        $this->listener->handle($this->event_mock);
    }

    /**
     * @test
     */
    public function if_discourse_response_code_is_not_200_log_a_notice_with_the_status_code()
    {
        $this->user_mock->id = 1;
        $this->event_mock->user = $this->user_mock;

        $this->logger_mock->shouldReceive('notice')->once();


        $configs = [
            'base_uri' => 'http://discourse.example.com',
            'headers'  => [
                'Api-Key'      => 'testkey',
                'Api-Username' => 'testuser',
            ],
        ];

        $this->response_mock->shouldReceive('getBody')
                            ->once()
                            ->andReturn(json_encode(['user' => $this->user_mock]));

        $this->response_mock->shouldReceive('getStatusCode')
                            ->andReturn(500);

        $this->response_mock->shouldReceive('getReasonPhrase')
                            ->once()
                            ->andReturn('Server error');

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
                          ->andReturn($this->response_mock);

        $this->guzzle_mock->shouldReceive('post')
                          ->with('admin/users/1/log_out')
                          ->andReturn($this->response_mock);

        $this->listener->handle($this->event_mock);
    }
}
