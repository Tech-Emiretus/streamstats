<?php

namespace Tests\Feature\Helpers;

use App\Helpers\Streams;
use App\Models\Game;
use App\Models\Tag;
use App\Models\User;
use App\Services\TwitchApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Mockery;
use stdClass;
use Tests\TestCase;

class StreamsTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_will_return_an_error_when_fetching_streams_from_twitch_fails()
    {
        $twitch = Mockery::mock(new TwitchApiService('aaaa', 'access_token'))
            ->allows([
                'getStreams' => null,
            ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something Unexpected Happened. Please check twitch configurations.');

        Streams::fetch($twitch);
    }

    /** @test */
    public function it_will_return_a_collection_of_live_streams()
    {
        $twitch = Mockery::mock(new TwitchApiService('aaaa', 'access_token'))
            ->allows([
                'getStreams' => (object) [
                    'data' => [
                        (object) ['type' => 'live'],
                        (object) ['type' => ''],
                        (object) ['type' => 'live'],
                    ]
                ],
            ]);

        $this->assertEquals(2, Streams::fetch($twitch)->count());
    }

    /** @test */
    public function it_can_take_a_specified_number_of_streams_from_returned_collection()
    {
        $twitch = Mockery::mock(new TwitchApiService('aaaa', 'access_token'))
            ->allows([
                'getStreams' => (object) [
                    'data' => [
                        (object) ['type' => 'live'],
                        (object) ['type' => 'live'],
                        (object) ['type' => 'live'],
                        (object) ['type' => 'live'],
                        (object) ['type' => 'live'],
                    ]
                ],
            ]);

        $this->assertEquals(3, Streams::fetch($twitch, 3)->count());
    }

    /** @test */
    public function it_can_process_collection_of_streams_fetched_from_twich()
    {
        $twitch = Mockery::mock(new TwitchApiService('aaaa', 'access_token'))
            ->allows([
                'getTags' => collect([
                    (object) [
                        'tag_id' => 'twitch_tag_id',
                        'is_auto' => true,
                        'localization_names' => (object) ['en-us' => 'English'],
                        'localization_descriptions' => (object) ['en-us' => 'English Game Tag'],
                    ]
                ]),
            ]);

        $data = json_encode([
            [
                'id' => 'twitch_stream_id',
                'title' => 'Twitch Stream',
                'viewer_count' => 1000,
                'thumbnail_url' => 'https://placeholder.com',
                'is_mature' => false,
                'language' => 'en',
                'started_at' => '2021-12-31 20:00:00',
                'user_id' => 'twitch_user_id',
                'user_login' => 'hacker',
                'user_name' => 'Hacker GH',
                'game_id' => 'twitch_game_id',
                'game_name' => 'God of War',
                'tag_ids' => ['twitch_tag_id']
            ]
        ]);

        $processed_stream = Streams::getProcessedStreams($twitch, collect(json_decode($data)))->first();
        $this->assertEquals('twitch_stream_id', $processed_stream['twitch_id']);
        $this->assertEquals('Twitch Stream', $processed_stream['title']);
        $this->assertEquals(1000, $processed_stream['viewer_count']);
        $this->assertEquals('https://placeholder.com', $processed_stream['thumbnail']);
        $this->assertEquals('en', $processed_stream['language']);
        $this->assertTrue(Carbon::parse('2021-12-31 20:00:00')->eq($processed_stream['started_at']));
        $this->assertFalse($processed_stream['is_mature']);

        $broadcaster = User::where('twitch_id', 'twitch_user_id')->first();
        $this->assertNotNull($broadcaster);
        $this->assertEquals($broadcaster->id, $processed_stream['broadcaster_id']);
        $this->assertEquals($broadcaster->username, 'hacker');
        $this->assertEquals($broadcaster->name, 'Hacker GH');

        $game = Game::where('twitch_id', 'twitch_game_id')->first();
        $this->assertNotNull($game);
        $this->assertEquals($game->id, $processed_stream['game_id']);
        $this->assertEquals($game->name, 'God of War');

        $tags = Tag::where('twitch_id', 'twitch_tag_id')->get();
        $this->assertFalse($tags->isEmpty());
        $this->assertCount(1, $tags);
        $this->assertSame($tags->pluck('id')->toArray(), $processed_stream['tags']);
    }
}
