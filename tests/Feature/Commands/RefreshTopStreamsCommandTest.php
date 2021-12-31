<?php

namespace Tests\Feature\Commands;

use App\Console\Commands\RefreshTopStreamsCommand;
use App\Models\TopStream;
use App\Services\TwitchApiService;
use App\Services\TwitchAuthService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Mockery\MockInterface;
use Tests\TestCase;

class RefreshTopStreamsCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_will_fail_when_system_access_token_cannot_be_retrieved_from_twitch()
    {
        $this->mock(TwitchAuthService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getSystemAccessToken')
                ->andReturn(null)
                ->once();
        });

        $this->artisan(RefreshTopStreamsCommand::class)
            ->assertFailed();
    }

    /** @test */
    public function test_refresh_top_streams()
    {
        $this->mock(TwitchAuthService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getSystemAccessToken')
                ->andReturn('asdfasdfasd')
                ->once();
        });

        $streams_response = [
            'data' => [
                [
                    'id' => 'twitch_stream_id',
                    'title' => 'Twitch Stream',
                    'viewer_count' => 1000,
                    'thumbnail_url' => 'https://placeholder.com',
                    'is_mature' => false,
                    'language' => 'en',
                    'type' => 'live',
                    'started_at' => '2021-12-31 20:00:00',
                    'user_id' => 'twitch_user_id',
                    'user_login' => 'hacker',
                    'user_name' => 'Hacker GH',
                    'game_id' => 'twitch_game_id',
                    'game_name' => 'God of War',
                    'tag_ids' => ['twitch_tag_id']
                ]
            ]
        ];

        $tags_response = [
            'data' => [
                [
                    'tag_id' => 'twitch_tag_id',
                    'is_auto' => true,
                    'localization_names' => ['en-us' => 'English'],
                    'localization_descriptions' => ['en-us' => 'English Game Tag'],
                ]
            ]
        ];

        Http::fake([
            TwitchApiService::ENDPOINT . '/streams?*' => Http::response($streams_response),
            TwitchApiService::ENDPOINT . '/tags/streams?*' => Http::response($tags_response)
        ]);

        $this->artisan(RefreshTopStreamsCommand::class)
            ->assertSuccessful();

        $top_streams = TopStream::where('twitch_id', 'twitch_stream_id')->get();
        $this->assertCount(1, $top_streams);
        $this->assertEquals('Twitch Stream', $top_streams->first()->title);
    }
}
