<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
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

    public function topStreams(): HasMany
    {
        return $this->hasMany(TopStream::class);
    }

    public static function getGameFromStream($stream): self
    {
        return static::firstOrCreate([
            'twitch_id' => $stream->game_id,
        ], [
            'name' => $stream->game_name,
        ]);
    }
}
