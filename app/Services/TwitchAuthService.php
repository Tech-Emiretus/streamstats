<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use InvalidArgumentException;
use stdClass;
use UnexpectedValueException;

class TwitchAuthService
{
    /**
     * Twitch's auth service endpoint.
     *
     * @static
     * @var string
     */
    public const ENDPOINT = 'https://id.twitch.tv';

    /**
     * Twitch App client ID.
     *
     * @var string
     */
    private string $client_id;

    /**
     * Twitch App client secret.
     *
     * @var string
     */
    private string $client_secret;

    /**
     * New instace of TwitchAuthService.
     *
     * @param string $client_id
     * @param string $client_secret
     * @return void
     * @throws UnexpectedValueException
     */
    public function __construct(string $client_id, string $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;

        if (empty($this->client_id) || empty($this->client_secret)) {
            throw new UnexpectedValueException('Twitch configuration is invalid.');
        }
    }

    /**
     * Returns the Twitch OAuth login url.
     *
     * @param string $redirect_url
     * @return string
     * @throws InvalidArgumentException
     */
    public function getLoginUrl(string $redirect_url): string
    {
        if (empty($redirect_url)) {
            throw new InvalidArgumentException('Redirect url cannot be null or empty.');
        }

        $url = static::ENDPOINT . '/oauth2/authorize';
        Session::put('twitch_state', (string) Str::uuid());

        $params = [
            'client_id' => $this->client_id,
            'redirect_uri' => $redirect_url,
            'response_type' => 'code',
            'scope' => 'user:read:follows user:read:email',
            'state' => Session::get('twitch_state'),
        ];

        return $url . '?' . http_build_query($params);
    }

    /**
     * Get the user access token using the authorization code from Twitch.
     *
     * @param string $code
     * @param string $redirect_url
     * @return null|string
     * @throws InvalidArgumentException
     */
    public function getUserAccessToken(string $code, string $redirect_url): ?string
    {
        if (empty($code) || empty($redirect_url)) {
            throw new InvalidArgumentException('Twitch code or redirect url cannot be null or empty.');
        }

        $response = Http::post(static::ENDPOINT . '/oauth2/token', [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $redirect_url
        ]);

        if ($response->successful() === false) {
            Log::error('User Access Token Error: ' . $response->body, 'twitch');
            return null;
        }

        $results = json_decode($response->body());

        return $results->access_token;
    }

    /**
     * Get the system access token. Used internally.
     *
     * @return null|string
     */
    public function getSystemAccessToken(): ?string
    {
        $response = Http::post(static::ENDPOINT . '/oauth2/token', [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credentials',
            'scope' => 'user:read:follows',
        ]);

        if ($response->successful() === false) {
            Log::error('System Access Token Error: ' . $response->body, 'twitch');
            return null;
        }

        $results = json_decode($response->body());

        return $results->access_token;
    }
}
