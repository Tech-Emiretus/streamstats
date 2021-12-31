<?php

namespace App\Http\Controllers;

use App\Helpers\Responder;
use App\Models\Tag;
use App\Models\TopStream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;

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
            return Responder::success([], 'User has no streams.');
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

    public function getMedianViewCount(Request $request): JsonResponse
    {
        $total_streams = TopStream::count();
        $is_even = $total_streams % 2 === 0;
        $offset = (int) floor($total_streams / 2);
        $offset = $is_even ? $offset - 1 : $offset;
        $limit = $is_even ? 2 : 1;

        $median_streams = TopStream::query()
            ->orderBy('viewer_count')
            ->offset($offset)
            ->limit($limit)
            ->pluck('viewer_count')
            ->toArray();

        $median = array_sum($median_streams) / count($median_streams);

        return Responder::success($median);
    }

    public function getStreamCountByHour(Request $request): JsonResponse
    {
        $sort_field = $request->input('sort_field', 'stream_hour');
        $sort_order = $request->input('sort_order', 'asc');
        $per_page = $request->input('per_page', 20);

        $streams = TopStream::query()
            ->select(DB::raw("DATE_FORMAT(started_at, '%Y-%m-%d %H:00:00') as stream_hour, count(id) as stream_count"))
            ->groupBy('stream_hour')
            ->orderBy($sort_field, $sort_order)
            ->paginate($per_page);

        return Responder::success($streams);
    }

    public function getViewerCountNeededForLowest(Request $request): JsonResponse
    {
        $user_lowest_stream = Auth::user()->streams()->min('viewer_count');

        if (is_null($user_lowest_stream)) {
            return Responder::success(null, 'User has no streams.');
        }

        $lowest_top_stream = TopStream::min('viewer_count');

        return Responder::success(max(0, $lowest_top_stream - $user_lowest_stream));
    }

    public function getSharedTags(Request $request): JsonResponse
    {
        $user_streams = Auth::user()->streams()->pluck('id');

        if ($user_streams->isEmpty()) {
            return Responder::success([], 'User has no streams.');
        }

        $sort_field = $request->input('sort_field', 'name');
        $sort_order = $request->input('sort_order', 'asc');
        $per_page = $request->input('per_page', 20);

        $top_streams = TopStream::pluck('id');

        $shared_tags = Tag::query()
            ->whereHas('userStreams', fn ($query) => $query->whereIn('user_streams.id', $user_streams))
            ->whereHas('topStreams', fn ($query) => $query->whereIn('top_streams.id', $top_streams))
            ->orderBy($sort_field, $sort_order)
            ->paginate($per_page);

        return Responder::success($shared_tags);
    }
}
