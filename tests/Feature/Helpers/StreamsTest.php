<?php

namespace Tests\Feature\Helpers;

use App\Helpers\Streams;
use App\Models\User;
use App\Services\TwitchApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class StreamsTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function it_will_return_an_error_when_fetching_top_streams_from_twitch_faisl()
    {
        $twitch = Mockery::mock(new TwitchApiService('aaaa', 'access_token'))
            ->allows([
                'getStreams' => null,
            ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Something Unexpected Happened. Please check twitch configurations.');

        Streams::fetch($twitch);
    }
}
