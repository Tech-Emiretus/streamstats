<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
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

    public static function firstOrCreateFromTwitch($twitch_user): self
    {
        return static::firstOrCreate([
            'twitch_id' => $twitch_user->id,
        ], [
            'username' => $twitch_user->login,
            'name' => $twitch_user->display_name,
            'email' => $twitch_user->email,
            'profile_image' => $twitch_user->profile_image_url,
        ]);
    }

    public static function getBroadcasterFromStream($stream): self
    {
        return static::firstOrCreate([
            'twitch_id' => $stream->user_id,
        ], [
            'username' => $stream->user_login,
            'name' => $stream->user_name,
        ]);
    }
}
