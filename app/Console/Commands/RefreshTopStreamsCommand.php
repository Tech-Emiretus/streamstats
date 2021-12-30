<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Tag;
use App\Models\TopStream;
use App\Models\User;
use App\Services\TwitchApiService;
use App\Services\TwitchAuthService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class RefreshTopStreamsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'top-streams:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refreshes the top 1000 streams';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(TwitchAuthService $auth_service): int
    {
        $start = Carbon::now();

        $access_token = $auth_service->getSystemAccessToken();

        if (is_null($access_token)) {
            $this->logError('Could not fetch access token from Twitch.');
            return Command::FAILURE;
        }

        $twitch = new TwitchApiService(Config::get('twitch.client_id'), $access_token);
        $streams = $this->fetchStreams($twitch);
        $processed_streams = $this->getProcessedStreams($streams->shuffle(), $twitch);

        $this->saveProcessedStreams($processed_streams);

        $duration = $start->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE);
        $this->info(PHP_EOL . 'Done. Top Streams have been refreshed. Time: ' . $duration);

        return Command::SUCCESS;
    }

    /**
     * Fetches the top 1000 streams from twitch. Some pages might not have full 100
     * live streams so we need to check that.
     *
     * @param TwitchApiService $twitch
     * @return Collection
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function fetchStreams(TwitchApiService $twitch): Collection
    {
        $top_streams = collect([]);
        $next_page_cursor = null;
        $page = 1;

        while ($top_streams->count() < 1000) {
            $this->comment('Fetching streams from page ' . $page . '. Currently at: ' . $top_streams->count() . ' streams.');

            $retries = 3;
            $streams = null;

            while ($retries > 0) {
                $streams = $twitch->getStreams(100, $next_page_cursor);

                if (!is_null($streams)) {
                    break;
                }

                $this->logError('Failed to fetch streams from twitch. Retrying');
                $retries--;
            }

            if (is_null($streams)) {
                throw new Exception('Something Unexpected Happened. Please check twitch configurations.');
            }

            collect($streams->data)->each(function ($stream) use ($top_streams) {
                if ($stream->type === 'live') {
                    $top_streams->push($stream);
                }
            });

            if ($streams->pagination) {
                $next_page_cursor = $streams->pagination->cursor;
            }

            $this->info('Completed fetching streams from page ' . $page . '. Currently at: ' . $top_streams->count() . ' streams.');
            $page++;
        }

        return $top_streams->take(1000);
    }

    /**
     * Get processed streams which are in the structure of our system architecture.
     *
     * @param Collection $streams
     * @return Collection
     */
    protected function getProcessedStreams(Collection $streams, TwitchApiService $twitch): Collection
    {
        $this->comment(PHP_EOL . 'Start processing streams.');

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

        $this->info('Done processing streams.');

        return $processed_streams;
    }

    /**
     * Save processed streams into the database.
     * This method replaces the nth stream in the db as such refreshing
     * a top stream one at a time. This is intentional as it might be the
     * least risky method of replacing all 1000 reviews during each refresh.
     *
     * @param Collection $streams
     * @return void
     */
    protected function saveProcessedStreams(Collection $streams): void
    {
        $this->comment(PHP_EOL . 'Start saving processed streams.');

        $streams->each(function ($stream, $key) {
            DB::transaction(function () use ($stream, $key) {
                $top_stream = TopStream::updateOrCreate(['id' => $key + 1], Arr::except((array) $stream, 'tags'));
                $top_stream->tags()->sync($stream['tags']);
            });
        });

        $this->info('Done saving processed streams.');
    }

    /**
     * Logs an error to the stdOut and also the laravel logs.
     *
     * @param string $message
     * @return void
     */
    protected function logError(string $message): void
    {
        $message = "Refresh Top Streams Command Error: {$message}";

        $this->error($message);
        Log::error($message);
    }
}
