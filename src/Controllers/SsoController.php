<?php

namespace Spinen\Discourse\Controllers;

use Cviebrock\DiscoursePHP\SSOHelper;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

/**
 * Class SsoController
 *
 * Controller to process the Discourse SSO request.  There is a good bit of logic in here that almost feels like too
 * much for a controller, but given that this is the only thing that this controller is doing, I am not going to break
 * it out into some service class.
 *
 * @package Spinen\Discourse
 */
class SsoController extends Controller
{
    /**
     * Package configuration
     *
     * @var Collection
     */
    protected $config;

    /**
     * SSOHelper Instance
     *
     * @var SSOHelper
     */
    protected $sso;

    /**
     * Authenticated user
     *
     * @var User
     */
    protected $user;

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
     * @return array
     */
    protected function buildExtraParameters()
    {
        return collect($this->config['user'])
            ->except(['external_id', 'email'])
            ->reject([$this, 'nullProperty'])
            ->map([$this, 'parseUserValue'])
            ->map([$this, 'castBooleansToString'])
            ->toArray();
    }

    /**
     * Make boolean's into string
     *
     * The Discourse SSO API does not accept 0 or 1 for false or true.  You must send
     * "false" or "true", so convert any boolean property to the string version.
     *
     * @param $property
     *
     * @return string
     */
    public function castBooleansToString($property)
    {
        if (! is_bool($property)) {
            return $property;
        }

        return ($property) ? 'true' : 'false';
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

        $this->user = $request->user();

        $query = $this->sso->getSignInString(
            $this->sso->getNonce($payload),
            $this->user->{$this->config['user']['external_id']},
            $this->user->{$this->config['user']['email']},
            $this->buildExtraParameters()
        );

        return redirect(str_finish($this->config['url'], '/').'session/sso_login?'.$query);
    }

    /**
     * Check to see if property is null
     *
     * @param string $property
     * @return bool
     */
    public function nullProperty($property)
    {
        return is_null($property);
    }

    /**
     * Get the property from the user
     *
     * If a string is passed in, then get it from the user object, otherwise, return what was given
     *
     * @param string $property
     * @return mixed
     */
    public function parseUserValue($property)
    {
        if (! is_string($property)) {
            return $property;
        }

        return $this->user->{$property};
    }
}
