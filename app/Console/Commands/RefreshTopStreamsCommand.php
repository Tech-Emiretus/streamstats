<?php

namespace App\Console\Commands;

use App\Helpers\Streams;
use App\Models\TopStream;
use App\Services\TwitchApiService;
use App\Services\TwitchAuthService;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

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

        // Fetch Streams
        $this->comment(PHP_EOL . 'Start fetching streams.');
        $streams = Streams::fetch($twitch, 1000);
        $this->info('Done fetching ' . $streams->count() . ' streams.');

        // Process Streams
        $this->comment(PHP_EOL . 'Start processing streams.');
        $processed_streams = Streams::getProcessedStreams($twitch, $streams);
        $this->info('Done processing streams.');

        // Save Streams
        $this->comment(PHP_EOL . 'Start saving processed streams.');
        $this->saveProcessedStreams($processed_streams);
        $this->info('Done saving processed streams.');

        $duration = $start->diffForHumans(syntax: CarbonInterface::DIFF_ABSOLUTE);
        $this->info(PHP_EOL . 'Done. Top Streams have been refreshed. Time: ' . $duration);

        return Command::SUCCESS;
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
        $streams->each(function ($stream, $key) {
            DB::transaction(function () use ($stream, $key) {
                $top_stream = TopStream::updateOrCreate(['id' => $key + 1], Arr::except((array) $stream, 'tags'));
                $top_stream->tags()->sync($stream['tags']);
            });
        });
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
