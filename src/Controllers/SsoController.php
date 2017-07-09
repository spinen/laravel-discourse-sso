<?php

namespace Spinen\Discourse\Controllers;

use Cviebrock\DiscoursePHP\SSOHelper;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Class SsoController
 *
 * Controller to process the Discourse SSO request
 *
 * @package Spinen\Discourse
 */
class SsoController extends Controller
{
    /**
     * Package configuration
     *
     * @var array
     */
    protected $config;

    /**
     * SSOHelper Instance
     *
     * @var SSOHelper
     */
    protected $sso;

    /**
     * SsoController constructor.
     *
     * @param Config $config
     * @param SSOHelper $sso
     */
    public function __construct(Config $config, SSOHelper $sso)
    {
        $this->config = $config->get('services.discourse');

        $this->sso = $sso->setSecret($this->config['secret']);
    }

    /**
     * Build out the extra parameters to send to Discourse
     *
     * @param $user
     *
     * @return array
     */
    protected function buildExtraParameters(User $user)
    {
        $parameters = [];

        // Only build array with extra properties (i.e. not external_id & email) & where the property is not null
        $user_properties = array_where(
            array_except($this->config['user'], ['external_id', "email"]),
            function ($value, $key) {
                return ! is_null($value);
            }
        );

        foreach ($user_properties as $key => $property) {
            $parameters[$key] = $this->parseUserValue($property, $user);
        }

        return $parameters;
    }

    /**
     * Process the SSO login request from Discourse
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function login(Request $request)
    {
        if (! ($this->sso->validatePayload($payload = $request->get('sso'), $request->get('sig')))) {
            abort(403); //Forbidden
        }

        $user = $request->user();

        $query = $this->sso->getSignInString(
            $this->sso->getNonce($payload),
            $user->{$this->config['user']['external_id']},
            $user->{$this->config['user']['email']},
            $this->buildExtraParameters($user)
        );

        return redirect(str_finish($this->config['url'], '/').'session/sso_login?'.$query);
    }

    /**
     * Get the property from the user
     *
     * If a string is passed in, then get it from the user object, otherwise, return what was given
     *
     * @param $property
     * @param User $user
     * @return mixed
     */
    public function parseUserValue($property, User $user)
    {
        if (! is_string($property)) {
            return $property;
        }

        return $user->{$property};
    }
}
