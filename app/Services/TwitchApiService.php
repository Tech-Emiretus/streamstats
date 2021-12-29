<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use stdClass;
use UnexpectedValueException;

class TwitchApiService
{
    /**
     * Twitch's auth service endpoint.
     *
     * @static
     * @var string
     */
    public const ENDPOINT = 'https://api.twitch.tv/helix';

    /**
     * Twitch App client ID.
     *
     * @var string
     */
    private string $client_id;

    /**
     * Twitch User or App access token.
     *
     * @var string
     */
    private string $access_token;

    /**
     * New instace of TwitchAuthService.
     *
     * @param string $client_id
     * @param string $client_secret
     * @return void
     * @throws UnexpectedValueException
     */
    public function __construct(string $client_id, string $access_token)
    {
        $this->client_id = $client_id;
        $this->access_token = $access_token;

        if (empty($this->client_id) || empty($this->access_token)) {
            throw new UnexpectedValueException('Twitch configuration is invalid.');
        }
    }

    /**
     * Get the authenticated user's details from twitch.
     *
     * @return null|stdClass
     * @throws InvalidArgumentException
     */
    public function getUser(): ?stdClass
    {
        $response = $this->fetch('get', static::ENDPOINT . '/users');

        if ($response->successful() === false) {
            Log::error('Get user failed: ' . $response->body(), 'twitch');
            return null;
        }

        $results = json_decode($response->body());

        return collect($results->data)->first();
    }

    /**
     * Fetch from the Twith Helix api.
     *
     * @param string $method
     * @param string $url
     * @param array $body
     * @return Response
     * @throws InvalidArgumentException
     */
    protected function fetch(string $method, string $url, array $body = []): Response
    {
        $httpClient = Http::acceptJson()
            ->withToken($this->access_token)
            ->withHeaders(['Client-ID' => $this->client_id]);

        return match (strtolower($method)) {
            'get' => $httpClient->get($url, $body),
            'post' => $httpClient->post($url, $body),
            default => throw new InvalidArgumentException("The specified http method ({$method}) is invalid.")
        };
    }
}
