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

    /**
     * @var Mockery\Mock
     */
    protected $user_mock;

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
        $this->sso_helper_mock->shouldReceive('setSecret')
                              ->once()
                              ->with('secret')
                              ->andReturnSelf();

        $this->user_mock = Mockery::mock(SSOHelper::class);
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

        $controller = new SsoController($this->config_mock, $this->sso_helper_mock);

        $this->assertInstanceOf(SsoController::class, $controller);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionCode 403
     */
    public function it_aborts_if_the_payload_is_invalid()
    {
        $this->config_mock->shouldReceive('get')
                          ->once()
                          ->with('services.discourse')
                          ->andReturn([
                              'secret' => 'secret',
                              'user'   => [
                                  'access' => null,
                              ],
                          ]);

        $this->request_mock->shouldReceive('user')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($this->user_mock);

        $this->request_mock->shouldReceive('get')
                           ->once()
                           ->with('sso')
                           ->andReturn('sso');

        $this->request_mock->shouldReceive('get')
                           ->once()
                           ->with('sig')
                           ->andReturn('sig');

        $this->sso_helper_mock->shouldReceive('validatePayload')
                              ->once()
                              ->withArgs(['sso', 'sig'])
                              ->andReturn(false);

        $controller = new SsoController($this->config_mock, $this->sso_helper_mock);

        $controller->login($this->request_mock);
    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionCode 403
     */
    public function it_is_backwards_compatible_with_config_that_does_not_have_access_key()
    {
        $this->config_mock->shouldReceive('get')
                          ->once()
                          ->with('services.discourse')
                          ->andReturn([
                              'secret' => 'secret',
                          ]);

        $this->request_mock->shouldReceive('user')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($this->user_mock);

        $this->request_mock->shouldReceive('get')
                           ->withAnyArgs()
                           ->andReturn('anything');

        $this->sso_helper_mock->shouldReceive('validatePayload')
                              ->once()
                              ->withAnyArgs()
                              ->andReturn(false); // Stop test here, as we know that we got past the access key

        $controller = new SsoController($this->config_mock, $this->sso_helper_mock);

        $controller->login($this->request_mock);

    }

    /**
     * @test
     * @expectedException Exception
     * @expectedExceptionCode 403
     */
    public function it_aborts_if_the_user_does_not_have_access()
    {
        $this->config_mock->shouldReceive('get')
                          ->once()
                          ->with('services.discourse')
                          ->andReturn([
                              'secret' => 'secret',
                              'user'   => [
                                  'access' => 'forum_access',
                              ],
                          ]);

        $this->user_mock->forum_access = false;

        $this->request_mock->shouldReceive('user')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($this->user_mock);

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
                                  'external_id'  => 'id',
                                  'email'        => 'email',
                                  // Expect this null_value to not be passed on
                                  'null_value'   => null,
                                  'false_value'  => false,
                                  'true_value'   => true,
                                  'string_value' => 'string',
                              ],
                          ]);

        $this->user_mock->id = 1;
        $this->user_mock->email = 'me@mydomain.tld';
        $this->user_mock->string = 'string_property';

        $this->request_mock->shouldReceive('user')
                           ->once()
                           ->withNoArgs()
                           ->andReturn($this->user_mock);

        $this->request_mock->shouldReceive('get')
                           ->once()
                           ->with('sso')
                           ->andReturn('sso');

        $this->request_mock->shouldReceive('get')
                           ->once()
                           ->with('sig')
                           ->andReturn('sig');

        $this->sso_helper_mock->shouldReceive('validatePayload')
                              ->once()
                              ->withArgs(['sso', 'sig'])
                              ->andReturn(true);

        $this->sso_helper_mock->shouldReceive('getNonce')
                              ->once()
                              ->with('sso')
                              ->andReturn('nonce');

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
