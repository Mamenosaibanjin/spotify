<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SongAudioFeature extends Model
{
    use HasFactory;
    
    /**
     * Die Tabelle, die mit diesem Model verbunden ist.
     *
     * @var string
     */
    protected $table = 'song_audio_features';
    
    /**
     * Die Attribute, die massenweise zuweisbar sind.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'song_id',
        'tempo',
        'loudness',
        'danceability',
        'energy',
        'acousticness',
        'instrumentalness',
    ];
    
    /**
     * Gibt den Song zurück, zu dem diese Audio-Features gehören.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function song()
    {
        return $this->belongsTo(Song::class);
    }
}
