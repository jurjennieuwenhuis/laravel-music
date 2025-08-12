<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Artist extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    public function records(): BelongsToMany
    {
        return $this->belongsToMany(Record::class, 'record_artists', 'artist_id', 'record_id');
    }

    public function setSpotifyCodeAttribute(?string $spotifyCode): void
    {
        // Extract the code from the url
        $regex = '/^https:\/\/open\.spotify\.com(\/embed)?\/artist\/([^\/?]*)?/';

        if (preg_match($regex, $spotifyCode, $matches)) {
            $spotifyCode = $matches[2];
        }

        $this->attributes['spotify_code'] = $spotifyCode;
    }

}
