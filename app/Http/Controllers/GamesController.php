<?php

namespace App\Http\Controllers;

use App\Helpers\Responder;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GamesController extends Controller
{
    public function getByStreamCount(Request $request): JsonResponse
    {
        $sort_field = $request->input('sort_field', 'name');
        $sort_order = $request->input('sort_order', 'asc');
        $per_page = $request->input('per_page', 20);

        $games = Game::withCount('topStreams as streams_count')
            ->whereHas('topStreams')
            ->orderBy($sort_field, $sort_order)
            ->paginate($per_page);

        return Responder::success($games);
    }

    public function getByViewerCount(Request $request): JsonResponse
    {
        $sort_field = $request->input('sort_field', 'name');
        $sort_order = $request->input('sort_order', 'asc');
        $per_page = $request->input('per_page', 20);

        $games = Game::withSum('topStreams as viewer_count', 'viewer_count')
            ->whereHas('topStreams')
            ->orderBy($sort_field, $sort_order)
            ->paginate($per_page);

        return Responder::success($games);
    }
}
