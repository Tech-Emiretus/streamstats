<?php

namespace App\Http\Controllers;

use App\Helpers\Responder;
use App\Models\TopStream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StreamStatsController extends Controller
{
    public function getTopStreams(Request $request): JsonResponse
    {
        $sort_field = $request->input('sort_field', 'viewer_count');
        $sort_order = $request->input('sort_order', 'desc');
        $limit = $request->input('top', 100);

        $streams = TopStream::with('game')
            ->orderBy($sort_field, $sort_order)
            ->limit($limit)
            ->get();

        return Responder::success($streams);
    }

    public function getStreamsFollowedByUser(Request $request): JsonResponse
    {
        $user_streams = Auth::user()->streams()->pluck('twitch_id');

        if ($user_streams->isEmpty()) {
            return Responder::success([]);
        }

        $sort_field = $request->input('sort_field', 'viewer_count');
        $sort_order = $request->input('sort_order', 'desc');
        $per_page = $request->input('per_page', 20);

        $streams = TopStream::with('game')
            ->whereIn('twitch_id', $user_streams)
            ->orderBy($sort_field, $sort_order)
            ->paginate($per_page);

        return Responder::success($streams);
    }

    public function getViewerCountNeededForLowest(Request $request): JsonResponse
    {
        $user_lowest_stream = Auth::user()->streams()->min('viewer_count');

        if (is_null($user_lowest_stream)) {
            return Responder::success([]);
        }

        $lowest_top_stream = TopStream::min('viewer_count');

        return Responder::success(max(0, $lowest_top_stream - $user_lowest_stream));
    }
}
