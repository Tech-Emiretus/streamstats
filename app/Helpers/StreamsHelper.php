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

class StreamsHelper
{
    /**
     * Fetches live streams from twitch with retry mechanism.
     * It can limit the number of streams to fetch by specifiying a number for the take param.
     *
     * @static
     * @param TwitchApiService $twitch
     * @param null|int $take
     * @return Collection
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public static function fetchStreams(TwitchApiService $twitch, ?int $take = null): Collection
    {
        $fetched_streams = collect([]);
        $next_page_cursor = null;
        $done = false;

        while (!$done) {
            $retries = 3;
            $streams = null;

            while ($retries > 0) {
                $streams = $twitch->getStreams(100, $next_page_cursor);

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

            if ($streams->pagination) {
                $next_page_cursor = $streams->pagination->cursor;
            }

            $done = !is_null($take) && $fetched_streams->count() >= $take;
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
}
