<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public static function getGameFromStream($stream): self
    {
        return static::firstOrCreate([
            'twitch_id' => $stream->game_id,
        ], [
            'name' => $stream->game_name,
        ]);
    }
}
