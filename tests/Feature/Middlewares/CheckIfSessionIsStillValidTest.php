<?php

namespace Tests\Feature\Middlewares;

use App\Http\Middleware\CheckIfSessionIsStillValid;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class CheckIfSessionIsStillValidTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @test */
    public function should_continue_request_when_user_is_unauthenticated()
    {
        try {
            $request = $this->mock(Request::class);
            $next_called = false;
            $next = function ($param) use (&$next_called) {
                $next_called = true;
            };

            (new CheckIfSessionIsStillValid())->handle($request, $next);
        } catch (HttpException $e) {
            $this->assertTrue(false, 'Should have continued with request with no user unathenticated.');
            return;
        }

        $this->assertTrue($next_called);
    }

    /** @test */
    public function should_continue_request_when_user_logged_in_less_than_an_hour_ago()
    {
        $user = User::factory()->create(['last_login_at' => Carbon::now()->subMinutes(55)]);
        $this->actingAs($user);

        try {
            $request = $this->mock(Request::class);
            $next_called = false;
            $next = function ($param) use (&$next_called) {
                $next_called = true;
            };

            (new CheckIfSessionIsStillValid())->handle($request, $next);
        } catch (HttpException $e) {
            $this->assertTrue(false, 'Should have continued with request since user logged in less than an hour ago.');
            return;
        }

        $this->assertTrue(Auth::check());
        $this->assertTrue($next_called);
    }

    /** @test */
    public function should_abort_request_when_user_logged_in_an_hour_or_more_than_an_hour_ago()
    {
        $user = User::factory()->create(['last_login_at' => Carbon::now()->subHour()]);
        $this->actingAs($user);

        try {
            $request = $this->mock(Request::class);
            $next_called = false;
            $next = function ($param) use (&$next_called) {
                $next_called = true;
            };

            (new CheckIfSessionIsStillValid())->handle($request, $next);
        } catch (HttpException $e) {
            $this->assertFalse(Auth::check());
            $this->assertFalse($next_called);
            $this->assertEquals(401, $e->getStatusCode());
            return;
        }

        $this->assertTrue(false, 'Should have continued with request since user logged in less than an hour ago.');
    }
}
