<?php

namespace Spinen\Discourse\Listeners;

use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

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
     * Create the event listener.
     *
     * @param Client $client
     *
     * @return void
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
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
            'base_uri' => config('services.discourse.url'),
            'headers'  => [
                'Api-Key'      => config('services.discourse.api.key'),
                'Api-Username' => config('services.discourse.api.user'),
            ],
        ];

        // Get Discourse user to match this one, and send a Logout request to Discourse and get the response
        $user = json_decode(
            $this->client->get("users/by-external/{$event->user->id}.json", $configs)
                         ->getBody()
        )->user;

        $response = $this->client->post("admin/users/{$user->id}/log_out");

        if ($response->getStatusCode() !== 200) {
            Log::notice(
                "When logging out user {$event->user->id} Discourse returned status code {$response->getStatusCode()}:",
                ['reason' => $response->getReasonPhrase()]
            );
        }
    }
}
