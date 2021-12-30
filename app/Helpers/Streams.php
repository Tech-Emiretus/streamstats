<?php

namespace App\Helpers;

use App\Models\Game;
use App\Models\Tag;
use App\Models\User;
use App\Services\TwitchApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class Streams
{
    /**
     * Fetches live streams from twitch with retry mechanism.
     * It can limit the number of streams to fetch by specifiying a number for the take param.
     *
     * @static
     * @param TwitchApiService $twitch
     * @param null|int $take
     * @param null|string $user_id
     * @return Collection
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function fetch(TwitchApiService $twitch, ?int $take = null, ?string $user_id = null): Collection
    {
        $fetched_streams = collect([]);
        $next_page_cursor = null;
        $done = false;
        $method = static::getFetchStreamsMethod($user_id);

        while (!$done) {
            $retries = 3;
            $streams = null;

            while ($retries > 0) {
                $streams = $twitch->$method(...static::getFetchStreamsArgs($next_page_cursor, $user_id));

                if (!is_null($streams)) {
                    break;
                }

                Log::error('Failed to fetch streams from twitch. Retrying');
                $retries--;
            }

            if (is_null($streams)) {
                throw new Exception('Something Unexpected Happened. Please check twitch configurations.');
            }

            collect($streams->data)->each(function ($stream) use ($fetched_streams) {
                if ($stream->type === 'live') {
                    $fetched_streams->push($stream);
                }
            });

            if ($streams->pagination && property_exists($streams->pagination, 'cursor')) {
                $next_page_cursor = $streams->pagination->cursor;
            }

            // Null cursor means there is no more data to be fetched.
            $done = is_null($next_page_cursor) || (!is_null($take) && $fetched_streams->count() >= $take);
        }

        return is_null($take) ? $fetched_streams : $fetched_streams->take($take);
    }

    /**
     * Get processed streams which are in the structure of our system architecture.
     *
     * @static
     * @param TwitchApiService $twitch
     * @param Collection $streams
     * @return Collection
     */
    public static function getProcessedStreams(TwitchApiService $twitch, Collection $streams): Collection
    {
        $processed_streams = collect([]);

        $streams->each(function ($stream) use ($processed_streams, $twitch) {
            $processed_streams->push([
                'twitch_id' => $stream->id,
                'title' => $stream->title,
                'viewer_count' => $stream->viewer_count,
                'thumbnail' => $stream->thumbnail_url,
                'is_mature' => $stream->is_mature,
                'language' => $stream->language,
                'started_at' => Carbon::parse($stream->started_at),
                'broadcaster_id' => User::getBroadcasterFromStream($stream)->id,
                'game_id' => Game::getGameFromStream($stream)->id,
                'tags' => Tag::getTagsFromStream($stream, $twitch),
            ]);
        });

        return $processed_streams;
    }

    public static function getFetchStreamsMethod(?string $user_id): string
    {
        return is_null($user_id) ? 'getStreams' : 'getFollowedStreams';
    }

    public static function getFetchStreamsArgs(?string $next_page_cursor = null, ?string $user_id = null): array
    {
        $args = [100, $next_page_cursor];

        if (!is_null($user_id)) {
            array_unshift($args, $user_id);
        }

        return $args;
    }
}
