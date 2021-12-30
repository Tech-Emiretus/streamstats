<?php

namespace App\Http\Controllers;

use App\Helpers\Responder;
use App\Helpers\Streams;
use App\Services\TwitchApiService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Throwable;

class UserStreamsController extends Controller
{
    public function refreshStreams()
    {
        $access_token = Session::get('twitch_access_token');

        if (is_null($access_token)) {
            Auth::logout();
            return redirect('/login')->withErrors('Could not retrieve your twitch access token from session.');
        }

        try {
            $user = Auth::user();

            $twitch = new TwitchApiService(Config::get('twitch.client_id'), $access_token);
            $streams = Streams::fetch(twitch: $twitch, user_id: $user->twitch_id);
            $processed_streams = Streams::getProcessedStreams($twitch, $streams);

            $user->refreshStreams($processed_streams);

            return Responder::success([], 'Refreshed streams successfully.');
        } catch (Throwable $e) {
            Log::error('Refresh streams error: ' . $e->getMessage());

            return Responder::error(
                [],
                'Something unexpected happened. Could not refresh streams.',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
