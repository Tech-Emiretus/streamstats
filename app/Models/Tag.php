<?php

namespace App\Models;

use App\Services\TwitchApiService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Tag extends Model
{
    use HasFactory;

    /**
     * The attributes that cannot be mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'id'
    ];

    public static function getTagsFromStream($stream, TwitchApiService $twitch): array
    {
        if (empty($stream->tag_ids)) {
            return [];
        }

        $existing_tags = static::query()
            ->whereIn('twitch_id', $stream->tag_ids)
            ->pluck('twitch_id')
            ->toArray();

        $non_existing_tags = array_diff($stream->tag_ids, $existing_tags);

        if (!empty($non_existing_tags)) {
            static::fetchAndSaveTagsFromTwitch($non_existing_tags, $twitch);
        }

        return static::query()
            ->whereIn('twitch_id', $stream->tag_ids)
            ->pluck('id')
            ->toArray();
    }

    public static function fetchAndSaveTagsFromTwitch(array $tags, TwitchApiService $twitch): void
    {
        $tags = $twitch->getTags($tags);

        if (is_null($tags)) {
            Log::error('Fetch stream tags failed.');
            return;
        }

        $locale = 'en-us';

        $tags->each(function ($tag) use ($locale) {
            static::updateOrCreate([
                'twitch_id' => $tag->tag_id,
            ], [
                'is_auto' => $tag->is_auto,
                'name' => $tag->localization_names->$locale,
                'description' => $tag->localization_descriptions->$locale,
            ]);
        });
    }
}
