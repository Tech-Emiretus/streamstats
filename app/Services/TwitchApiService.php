<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
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
     * Minimum number of records to be fetched per page.
     *
     * @static
     * @var int
     */
    public const MIN_PER_PAGE = 20;

    /**
     * Maximum number of records to be fetched per page.
     *
     * @static
     * @var int
     */
    public const MAX_PER_PAGE = 100;

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
     */
    public function getUser(): ?stdClass
    {
        $response = $this->fetch('get', static::ENDPOINT . '/users');

        if ($response->successful() === false) {
            Log::error('Get user failed: ' . $response->body());
            return null;
        }

        $results = json_decode($response->body());

        return collect($results->data)->first();
    }

    /**
     * Get the current streams from twitch sorted by descending order of viewer count.
     *
     * @param int $per_page
     * @param null|string $after
     * @return null|stdClass
     */
    public function getStreams(int $per_page = 20, ?string $after = null): ?stdClass
    {
        $params = [
            'first' => static::getPerPage($per_page),
        ];

        if (!empty($after)) {
            $params['after'] = $after;
        }

        $response = $this->fetch('get', static::ENDPOINT . '/streams', $params);

        if ($response->successful() === false) {
            Log::error('Get streams failed: ' . $response->body());
            return null;
        }

        return json_decode($response->body());
    }

    /**
     * Get the followed streams from twitch sorted by descending order of viewer count.
     *
     * @param string $twitch_user_id
     * @param int $per_page
     * @param null|string $after
     * @return null|stdClass
     */
    public function getFollowedStreams(string $twitch_user_id, int $per_page = 20, ?string $after = null): ?stdClass
    {
        $params = [
            'user_id' => $twitch_user_id,
            'first' => static::getPerPage($per_page),
        ];

        if (!empty($after)) {
            $params['after'] = $after;
        }

        $response = $this->fetch('get', static::ENDPOINT . '/streams/followed', $params);

        if ($response->successful() === false) {
            Log::error('Get followed streams failed: ' . $response->body());
            return null;
        }

        return json_decode($response->body());
    }

    /**
     * Get tag details from twitch for the specified tag ids.
     *
     * @param array $tag_ids
     * @return null|Collection
     * @throws InvalidArgumentException
     */
    public function getTags(array $tag_ids): ?Collection
    {
        if (empty($tag_ids)) {
            throw new InvalidArgumentException('Specified tag ids array cannot be empty.');
        }

        $params = [];

        foreach ($tag_ids as $tag_id) {
            $params['tag_id'] = $tag_id;
        }

        $response = $this->fetch('get', static::ENDPOINT . '/tags/streams', $params);

        if ($response->successful() === false) {
            Log::error('Get tags failed: ' . $response->body());
            return null;
        }

        $result = json_decode($response->body());
        return collect($result->data);
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

    /**
     * Gets the valid per page value to use.
     * Uses the min value when per page is below accepted value.
     * Uses the max value when per page is above accepted value.
     *
     * @static
     * @param int $per_page
     * @return int
     */
    protected static function getPerPage(int $per_page): int
    {
        return max(static::MIN_PER_PAGE, min(static::MAX_PER_PAGE, $per_page));
    }
}
