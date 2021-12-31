<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use App\Services\TwitchApiService;
use App\Services\TwitchAuthService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Mockery\MockInterface;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function can_get_login_page_with_twitch_login_url_and_state()
    {
        $url = TwitchAuthService::ENDPOINT . '/oauth2/authorize';
        $params = [
            'client_id' => 'client_id',
            'redirect_uri' => 'redirect_url',
            'response_type' => 'code',
            'scope' => 'user:read:follows user:read:email',
            'state' => 'twitch_state',
        ];

        $login_url = $url . '?' . http_build_query($params);

        $this->mock(TwitchAuthService::class, function (MockInterface $mock) use ($login_url) {
            $mock->shouldReceive('getLoginUrl')
                ->andReturn($login_url)
                ->once();
        });

        $this->get('/login')
            ->assertViewIs('login')
            ->assertViewHas('login_url', $login_url);
    }

    /** @test */
    public function it_will_redirect_to_login_with_error_when_authenticate_is_called_without_twitch_code()
    {
        $this->withSession(['twitch_state' => '111111'])
            ->get('/authenticate')
            ->assertRedirect('/login')
            ->assertSessionHasErrors();
    }

    /** @test */
    public function it_will_redirect_to_login_with_error_when_authenticate_is_called_without_twitch_state()
    {
        $this->withSession(['twitch_state' => '111111'])
            ->get('/authenticate?code=asdfassdfa')
            ->assertRedirect('/login')
            ->assertSessionHasErrors();
    }

    /** @test */
    public function it_will_redirect_to_login_with_error_when_twitch_user_access_token_could_not_be_fetched()
    {
        $this->mock(TwitchAuthService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getUserAccessToken')
                ->andReturn(null)
                ->once();
        });

        $this->withSession(['twitch_state' => '111111'])
            ->get('/authenticate?code=asdfassdfa&state=111111')
            ->assertRedirect('/login')
            ->assertSessionHasErrors();
    }

    /** @test */
    public function it_will_redirect_to_login_with_error_when_twitch_user_could_not_be_fetched()
    {
        $this->mock(TwitchAuthService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getUserAccessToken')
                ->andReturn('access_token')
                ->once();
        });

        Http::fake([
            TwitchApiService::ENDPOINT . '/users' => Http::response([], 400)
        ]);

        $this->withSession(['twitch_state' => '111111'])
            ->get('/authenticate?code=asdfassdfa&state=111111')
            ->assertRedirect('/login')
            ->assertSessionHasErrors();
    }

    /** @test */
    public function it_will_store_and_login_twitch_user_when_authenticate_is_successful()
    {
        $this->mock(TwitchAuthService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getUserAccessToken')
                ->andReturn('access_token')
                ->once();
        });

        $twitch_user = json_encode([
            'data' => [
                [
                    'id' => 1111,
                    'login' => 'asd',
                    'display_name' => 'AS D',
                    'email' => 'asd@gmail.com',
                    'profile_image_url' => 'http://profile_image.com',
                ]
            ]
        ]);

        Http::fake([
            TwitchApiService::ENDPOINT . '/users' => Http::response($twitch_user, 200)
        ]);

        $this->withSession(['twitch_state' => '111111'])
            ->get('/authenticate?code=asdfassdfa&state=111111')
            ->assertRedirect('/');

        $user = User::where('twitch_id', 1111)->first();

        $this->assertTrue(Auth::check());
        $this->assertEquals('access_token', Session::get('twitch_access_token'));
        $this->assertNotNull($user);
        $this->assertEquals('asd@gmail.com', $user->email);
        $this->assertNotNull($user->last_login_at);
    }

    /** @test */
    public function can_get_authenticated_user_details()
    {
        $user = User::factory()->create(['last_login_at' => Carbon::now()]);

        $this->actingAs($user)
            ->json('GET', '/user')
            ->assertSuccessful()
            ->assertJson([
                'success' => true,
                'message' => '',
                'data' => [
                    'id' => $user->id,
                    'twitch_id' => $user->twitch_id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                ]
            ]);
    }

    /** @test */
    public function can_logout_authenticated_user()
    {
        $user = User::factory()->create(['last_login_at' => Carbon::now()]);
        $this->actingAs($user);
        $this->withSession(['twitch_access_token' => 'meme']);

        $this->assertTrue(Auth::check());
        $this->assertEquals('meme', Session::get('twitch_access_token'));

        $this->get('/logout')
            ->assertRedirect('/login');

        $this->assertFalse(Auth::check());
        $this->assertNull(Session::get('twitch_access_token'));
    }
}
