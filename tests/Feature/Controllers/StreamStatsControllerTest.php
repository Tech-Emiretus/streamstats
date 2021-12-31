<?php

namespace Tests\Feature\Controllers;

use App\Models\Game;
use App\Models\Tag;
use App\Models\TopStream;
use App\Models\User;
use App\Models\UserStream;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class StreamStatsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['last_login_at' => Carbon::now()]));
    }

    /**
     * @dataProvider topStreamsDataProvider
     * @test
     */
    public function test_top_streams($data, $top, $order, $expected)
    {
        foreach ($data as $viewer_count) {
            TopStream::factory()->create(['viewer_count' => $viewer_count]);
        }

        $response = $this->get("/streams/top-streams?top={$top}&sort_order={$order}")
            ->assertSuccessful()
            ->json('data');

        $topViewerCounts = collect($response)->pluck('viewer_count')->toArray();

        $this->assertEquals($expected, $topViewerCounts);
    }

    /** @test */
    public function it_will_return_empty_data_when_getting_streams_followed_by_user_when_user_has_no_data()
    {
        TopStream::factory()->count(100)->create();

        $this->get('/streams/followed-by-user')
            ->assertSuccessful()
            ->assertJson([
                'success' => true,
                'message' => 'User has no streams.',
                'data' => [],
            ]);

        $this->assertEquals(100, TopStream::count());
    }

    /** @test */
    public function can_return_data_when_getting_streams_followed_by_user()
    {
        $shared_streams = TopStream::factory()->count(100)->create()->take(5)->pluck('twitch_id');

        $shared_streams->each(function ($twitch_id) {
            UserStream::factory()->create([
                'user_id' => Auth::id(),
                'twitch_id' => $twitch_id
            ]);
        });

        $response = $this->get('/streams/followed-by-user')
            ->assertSuccessful()
            ->assertJsonCount(5, 'data.data')
            ->json('data.data');

        $followed_streams = collect($response)->pluck('twitch_id');

        $this->assertTrue($followed_streams->diff($shared_streams)->values()->isEmpty());
        $this->assertEquals(100, TopStream::count());
        $this->assertEquals(5, UserStream::count());
    }

    /** @test */
    public function it_will_return_the_min_views_needed_for_a_user_stream_to_make_a_top_stream()
    {
        TopStream::factory()->count(100)->create(['viewer_count' => random_int(100, 1000)]);
        $lowest_top_stream = TopStream::factory()->create(['viewer_count' => 99]);

        UserStream::factory()->count(10)->create(['user_id' => Auth::id(), 'viewer_count' => random_int(50, 100)]);
        $lowest_user_stream = UserStream::factory()->create(['user_id' => Auth::id(), 'viewer_count' => 45]);

        $this->get('/streams/min-viewer-count-needed')
            ->assertSuccessful()
            ->assertJson([
                'data' => 54
            ]);
    }

    /** @test */
    public function returns_zero_as_the_min_views_needed_for_a_user_stream_to_make_a_top_stream_when_all_user_streams_are_top_streams()
    {
        TopStream::factory()->count(100)->create(['viewer_count' => random_int(100, 1000)]);
        $lowest_top_stream = TopStream::factory()->create(['viewer_count' => 99]);

        UserStream::factory()->count(10)->create(['user_id' => Auth::id(), 'viewer_count' => random_int(150, 400)]);

        $this->get('/streams/min-viewer-count-needed')
            ->assertSuccessful()
            ->assertJson([
                'data' => 0
            ]);
    }

    /** @test */
    public function returns_null_as_the_min_views_needed_for_a_user_stream_to_make_a_top_stream_when_user_has_no_streams()
    {
        TopStream::factory()->count(100)->create(['viewer_count' => random_int(100, 1000)]);

        $this->get('/streams/min-viewer-count-needed')
            ->assertSuccessful()
            ->assertJson([
                'data' => null
            ]);
    }

    /** @test */
    public function can_get_shared_tags_between_user_streams_and_top_streams()
    {
        $shared_tags = collect([]);
        $shared_streams = TopStream::factory()->count(100)->create()->take(5);

        $shared_streams->each(function ($stream) use ($shared_tags) {
            $tag = Tag::factory()->create();

            $stream->tags()->attach($tag);

            UserStream::factory()
                ->hasAttached($tag)
                ->create([
                    'user_id' => Auth::id(),
                    'twitch_id' => $stream->twitch_id
                ]);

            $shared_tags->push($tag);
        });

        $response = $this->get('/streams/shared-tags')
            ->assertSuccessful()
            ->assertJsonCount(5, 'data.data')
            ->json('data.data');

        $retrieved_tags = collect($response)->pluck('twitch_id');

        $this->assertTrue($retrieved_tags->diff($shared_tags->pluck('twitch_id'))->values()->isEmpty());
        $this->assertEquals(100, TopStream::count());
        $this->assertEquals(5, UserStream::count());
        $this->assertEquals(5, Tag::count());
    }

    /**
     * @dataProvider medianViewCountDataProvider
     * @test
     */
    public function test_get_median_view_count($data, $expected)
    {
        foreach ($data as $viewer_count) {
            TopStream::factory()->create(['viewer_count' => $viewer_count]);
        }

        $this->get('/streams/median-view-count')
            ->assertSuccessful()
            ->assertJson([
                'data' => $expected
            ]);
    }

    protected function topStreamsDataProvider(): array
    {
        return [
            'With no streams' => [
                [], 5, 'ASC', []
            ],
            'top 3 ASC' => [
                [20, 1, 6, 123, 55], 3, 'ASC', [20, 55, 123]
            ],
            'top 3 DESC' => [
                [20, 1, 6, 123, 55], 3, 'DESC', [123, 55, 20]
            ],
            'top 5 ASC' => [
                [20, 1, 123, 55], 5, 'ASC', [1, 20, 55, 123]
            ],
            'top 5 DESC' => [
                [20, 1, 123, 55], 5, 'DESC', [123, 55, 20, 1]
            ]
        ];
    }

    protected function medianViewCountDataProvider(): array
    {
        return [
            'With no streams' => [
                [], 0
            ],
            'With odd number of streams' => [
                [20, 1, 6, 123, 55], 20
            ],
            'With event number of streams' => [
                [20, 1, 123, 55], 37.5
            ]
        ];
    }
}
