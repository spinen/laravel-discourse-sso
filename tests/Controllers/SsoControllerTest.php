<?php

namespace Spinen\Discourse\Controllers;

use Cviebrock\DiscoursePHP\SSOHelper;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Mockery;
use Spinen\Discourse\TestCase;

class SsoControllerTest extends TestCase
{
    /**
     * @var Mockery\Mock
     */
    protected $config_mock;

    /**
     * @var Mockery\Mock
     */
    protected $request_mock;

    /**
     * @var Mockery\Mock
     */
    protected $sso_helper_mock;

    public function setUp()
    {
        parent::setUp();

        $this->setUpMocks();
    }

    private function setUpMocks()
    {
        $this->config_mock = Mockery::mock(Config::class);
        $this->request_mock = Mockery::mock(Request::class);
        $this->sso_helper_mock = Mockery::mock(SSOHelper::class);
    }

    /**
     * @test
     */
    public function it_can_be_constructed()
    {
        $this->config_mock->shouldReceive('get')
                          ->once()
                          ->with('services.discourse')
                          ->andReturn([
                                  'secret' => 'secret',
                              ]);

        $this->sso_helper_mock->shouldReceive('setSecret')
                              ->once()
                              ->withAnyArgs()
                              ->andReturnSelf();

        $controller = new SsoController($this->config_mock, $this->sso_helper_mock);

        $this->assertInstanceOf(SsoController::class, $controller);
    }

    /**
     * @test
     */
    public function it_uses_the_configured_secret_with_the_helper()
    {
        $this->config_mock->shouldReceive('get')
                          ->once()
                          ->with('services.discourse')
                          ->andReturn([
                                  'secret' => 'secret',
                              ]);

        $this->sso_helper_mock->shouldReceive('setSecret')
                              ->once()
                              ->with('secret')
                              ->andReturnSelf();

        new SsoController($this->config_mock, $this->sso_helper_mock);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionCode 403
     */
    public function it_aborts_if_the_paylod_is_invalid()
    {
        $this->config_mock->shouldReceive('get')
                          ->once()
                          ->with('services.discourse')
                          ->andReturn([
                                  'secret' => 'secret',
                              ]);

        $this->sso_helper_mock->shouldReceive('setSecret')
                              ->once()
                              ->withAnyArgs()
                              ->andReturnSelf();

        $this->sso_helper_mock->shouldReceive('validatePayload')
                              ->once()
                              ->withArgs(['sso', 'sig'])
                              ->andReturn(false);

        $this->request_mock->shouldReceive('get')
                           ->once()
                           ->with('sso')
                           ->andReturn('sso');

        $this->request_mock->shouldReceive('get')
                           ->once()
                           ->with('sig')
                           ->andReturn('sig');

        $controller = new SsoController($this->config_mock, $this->sso_helper_mock);

        $controller->login($this->request_mock);
    }

    /**
     * @test
     */
    public function it_builds_the_correct_payload()
    {
        $this->config_mock->shouldReceive('get')
                          ->once()
                          ->with('services.discourse')
                          ->andReturn([
                              'secret' => 'secret',
                              // Expect the '/' on the end to not double up
                              'url'    => 'http://discourse/',
                              'user'   => [
                                  'external_id' => 'id',
                                  'email'       => 'email',
                                  // Expect this null_value to not be passed on
                                  'null_value'  => null,
                                  'false_value' => false,
                                  'true_value'  => true,
                                  z
                                      'string_value' => 'string',
                                  ],
                              ]
                          );

        $this->sso_helper_mock->shouldReceive('setSecret')
                              ->once()
                              ->withAnyArgs()
                              ->andReturnSelf();

        $this->sso_helper_mock->shouldReceive('validatePayload')
                              ->once()
                              ->withArgs(['sso', 'sig'])
                              ->andReturn(true);

        $this->request_mock->shouldReceive('get')
                           ->once()
                           ->with('sso')
                           ->andReturn('sso');

        $this->request_mock->shouldReceive('get')
                           ->once()
                           ->with('sig')
                           ->andReturn('sig');

        $user_mock = Mockery::mock(User::class);

        $this->request_mock->shouldReceive('user')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($user_mock);

        $this->sso_helper_mock->shouldReceive('getNonce')
                              ->once()
                              ->with('sso')
                              ->andReturn('nonce');

        $user_mock->id = 1;
        $user_mock->email = 'me@mydomain.tld';
        $user_mock->string = 'string_property';

        $this->sso_helper_mock->shouldReceive('getSignInString')
                              ->once()
                              ->withArgs([
                                      'nonce',
                                      1,
                                      'me@mydomain.tld',
                                      [
                                          'false_value'  => 'false',
                                          'true_value'   => 'true',
                                          'string_value' => 'string_property',
                                      ],
                                  ])
                              ->andReturn('query');

        $controller = new SsoController($this->config_mock, $this->sso_helper_mock);

        $this->assertEquals('http://discourse/session/sso_login?query', $controller->login($this->request_mock));
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
