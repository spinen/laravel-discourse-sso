<?php

namespace Spinen\Discourse\Listeners;

use GuzzleHttp\Client;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Log\Logger;

/**
 * Class LogoutDiscourseUser
 *
 * Send a Logout request to Discourse for the corresponding Laravel User.
 *
 * @package Spinen\Discourse\Listeners
 */
class LogoutDiscourseUser implements ShouldQueue
{
    /**
     * @var Client
     */
    public $client;

    /**
     * @var Repository
     */
    public $config_repository;

    /**
     * @var Logger
     */
    public $logger;

    /**
     * Create the event listener.
     *
     * @param Client $client
     * @param Repository $config_repository
     * @param Logger $logger
     *
     * @return void
     */
    public function __construct(Client $client, Repository $config_repository, Logger $logger)
    {
        $this->client = $client;
        $this->config_repository = $config_repository;
        $this->logger = $logger;
    }

    /**
     * Handle the event.
     *
     * @param mixed $event
     *
     * @return void
     */
    public function handle($event)
    {
        if (!$event->user) {
            return;
        }

        $configs = [
            'base_uri' => $this->config_repository->get('services.discourse.url')
        ];

        // Get Discourse user to match this one, and send a Logout request to Discourse and get the response
        $user = json_decode(
            $this->client->get("users/by-external/{$event->user->id}.json", $configs)
                         ->getBody()
        )->user;
        

        $configs = [
            'base_uri' => $this->config_repository->get('services.discourse.url'),
            'headers'  => [
                'Api-Key'      => $this->config_repository->get('services.discourse.api_key'),
                'Api-Username' => $user->username,
            ],
        ];

        $response = $this->client->post("admin/users/{$user->id}/log_out", $configs);

        if ($response->getStatusCode() !== 200) {
            $this->logger->notice(
                "When logging out user {$event->user->id} Discourse returned status code {$response->getStatusCode()}:",
                ['reason' => $response->getReasonPhrase()]
            );
        }
    }
}
