<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


/**
 * Class Record
 */
class Record extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * @var string
     */
    protected $table = 'records';

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(
            Genre::class,
            'record_genres',
            'record_id',
            'genre_id'
        )->orderBy('name');
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(
            Artist::class,
            'record_artists',
            'record_id',
            'artist_id'
        )->orderBy('name');
    }

    public function setSpotifyCodeAttribute(?string $spotifyCode): void
    {
        // Extract the code from the url
        $regex = '/^https:\/\/open\.spotify\.com(\/embed)?\/album\/([^\/?]*)?/';

        if (preg_match($regex, $spotifyCode, $matches)) {
            $spotifyCode = $matches[2];
        }

        $this->attributes['spotify_code'] = $spotifyCode;
    }
}
