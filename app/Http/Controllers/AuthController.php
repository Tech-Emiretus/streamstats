<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TwitchApiService;
use App\Services\TwitchAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

class AuthController extends Controller
{
    public function login(Request $request, TwitchAuthService $auth_service)
    {
        $redirect_url = URL::to(Config::get('twitch.user_redirect_url'));
        $login_url = $auth_service->getLoginUrl($redirect_url);

        return view('login', compact('login_url'));
    }

    public function authenticate(Request $request, TwitchAuthService $auth_service)
    {
        // todo: move to form request
        if (!$request->input('code') || $request->input('state') !== Session::get('twitch_state')) {
            return redirect('/login')->withErrors('Something unexpected happened.');
        }

        $redirect_url = URL::to(Config::get('twitch.user_redirect_url'));
        $access_token = $auth_service->getUserAccessToken($request->input('code'), $redirect_url);

        if (is_null($access_token)) {
            return redirect('/login')
                ->withErrors('Something unexpected happened. Could not fetch access token.');
        }

        $twitch_api = new TwitchApiService(Config::get('twitch.client_id'), $access_token);
        $twitch_user = $twitch_api->getUser();

        if (is_null($twitch_user)) {
            return redirect('/login')
                ->withErrors('Something unexpected happened. Could not fetch user details from Twitch.');
        }

        $user = User::firstOrCreateFromTwitch($twitch_user);
        Auth::login($user);

        static::saveAccessToken($access_token);

        return redirect('/');
    }

    public function getUser()
    {
        return Auth::user();
    }

    public function logout()
    {
        Auth::logout();
        Session::remove('twitch_access_token');

        return redirect('/login');
    }

    protected static function saveAccessToken(string $access_token): void
    {
        Session::put('twitch_access_token', $access_token);
    }
}
