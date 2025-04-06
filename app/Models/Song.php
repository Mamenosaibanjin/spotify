<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Song extends Model
{
    use HasFactory;
    
    /**
     * Die Tabelle, die mit diesem Model verbunden ist.
     *
     * @var string
     */
    protected $table = 'songs';
    
    /**
     * Die Attribute, die massenweise zuweisbar sind.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'spotify_id',
        'artist',
        'album',
        'duration',
        'preview_url',
        'release_date',
    ];
    
    /**
     * Gibt die Playlists zurück, in denen dieser Song enthalten ist.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_song')
        ->withTimestamps();
    }
    
    /**
     * Gibt die Audio-Features für diesen Song zurück.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function audioFeature()
    {
        return $this->hasOne(SongAudioFeature::class, 'song_id');
    }
}
