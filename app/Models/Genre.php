<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Genre extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'genres';

    public function records(): BelongsToMany
    {
        return $this->belongsToMany(
            Record::class,
            'record_genres',
            'genre_id',
            'record_id'
        );
    }
}
