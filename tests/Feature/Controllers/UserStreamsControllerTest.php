<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Services\TwitchApiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserStreamsControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['last_login_at' => Carbon::now()]));
    }

    /** @test */
    public function it_will_logout_and_redirect_to_login_when_twitch_access_token_is_not_in_session()
    {
        $this->assertTrue(Auth::check());

        $this->get('/refresh-streams')
            ->assertRedirect('/login')
            ->assertSessionHasErrors();

        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function it_will_return_an_error_when_fetching_from_twitch_faisl()
    {
        Http::fake([
            TwitchApiService::ENDPOINT . '/streams?*' => Http::response([], 400)
        ]);

        $this->withSession(['twitch_access_token' => '111111'])
            ->get('/refresh-streams')
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'success' => false,
                'error' => 'Something unexpected happened. Could not refresh streams.'
            ]);
    }
}
