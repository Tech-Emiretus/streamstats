<?php

namespace Tests\Feature\Controllers;

use App\Models\Game;
use App\Models\TopStream;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class GamesControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public $game_A;
    public $game_B;
    public $game_C;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupTestData();
        $this->actingAs(User::factory()->create(['last_login_at' => Carbon::now()]));
    }

    /**
     * @dataProvider getByStreamCountDataProvider
     * @test
     */
    public function can_get_games_by_stream_count($params, $expected)
    {
        $this->json('GET', '/games/by-stream-count', $params)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'data' => $expected
                ]
            ]);
    }

    /**
     * @dataProvider getByViewerCountDataProvider
     * @test
     */
    public function can_get_games_by_viewer_count($params, $expected)
    {
        $this->json('GET', '/games/by-viewer-count', $params)
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'data' => $expected
                ]
            ]);
    }

    protected function getByStreamCountDataProvider(): array
    {
        return [
            'Order by name ASC' => [
                ['sort_field' => 'name', 'sort_order' => 'ASC'],
                [
                    ['id' => 1, 'streams_count' => 5],
                    ['id' => 2, 'streams_count' => 1],
                    ['id' => 3, 'streams_count' => 3],
                ]
            ],
            'Order by name DESC' => [
                ['sort_field' => 'name', 'sort_order' => 'DESC'],
                [
                    ['id' => 3, 'streams_count' => 3],
                    ['id' => 2, 'streams_count' => 1],
                    ['id' => 1, 'streams_count' => 5],
                ]
            ],
            'Order by streams count ASC' => [
                ['sort_field' => 'streams_count', 'sort_order' => 'ASC'],
                [
                    ['id' => 2, 'streams_count' => 1],
                    ['id' => 3, 'streams_count' => 3],
                    ['id' => 1, 'streams_count' => 5],
                ]
            ],
            'Order by streams count DESC' => [
                ['sort_field' => 'streams_count', 'sort_order' => 'DESC'],
                [
                    ['id' => 1, 'streams_count' => 5],
                    ['id' => 3, 'streams_count' => 3],
                    ['id' => 2, 'streams_count' => 1],
                ]
            ],
        ];
    }

    protected function getByViewerCountDataProvider(): array
    {
        return [
            'Order by name ASC' => [
                ['sort_field' => 'name', 'sort_order' => 'ASC'],
                [
                    ['id' => 1, 'viewer_count' => 25],
                    ['id' => 2, 'viewer_count' => 160],
                    ['id' => 3, 'viewer_count' => 120],
                ]
            ],
            'Order by name DESC' => [
                ['sort_field' => 'name', 'sort_order' => 'DESC'],
                [
                    ['id' => 3, 'viewer_count' => 120],
                    ['id' => 2, 'viewer_count' => 160],
                    ['id' => 1, 'viewer_count' => 25],
                ]
            ],
            'Order by streams count ASC' => [
                ['sort_field' => 'viewer_count', 'sort_order' => 'ASC'],
                [
                    ['id' => 1, 'viewer_count' => 25],
                    ['id' => 3, 'viewer_count' => 120],
                    ['id' => 2, 'viewer_count' => 160],
                ]
            ],
            'Order by streams count DESC' => [
                ['sort_field' => 'viewer_count', 'sort_order' => 'DESC'],
                [
                    ['id' => 2, 'viewer_count' => 160],
                    ['id' => 3, 'viewer_count' => 120],
                    ['id' => 1, 'viewer_count' => 25],
                ]
            ],
        ];
    }

    protected function setupTestData()
    {
        $this->game_A = Game::factory()->create(['id' => 1, 'name' => 'A']);
        $this->game_B = Game::factory()->create(['id' => 2, 'name' => 'B']);
        $this->game_C = Game::factory()->create(['id' => 3, 'name' => 'C']);

        TopStream::factory()->count(5)->create([
            'game_id' => 1,
            'viewer_count' => 5
        ]);

        TopStream::factory()->count(1)->create([
            'game_id' => 2,
            'viewer_count' => 160,
        ]);

        TopStream::factory()->count(3)->create([
            'game_id' => 3,
            'viewer_count' => 40
        ]);
    }
}
