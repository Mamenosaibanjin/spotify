<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;
    
    /**
     * Die Tabelle, die mit diesem Model verbunden ist.
     *
     * @var string
     */
    protected $table = 'playlists';
    
    /**
     * Die Attribute, die massenweise zuweisbar sind.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'cover_path',
    ];
    
    /**
     * Gibt die Songs zurück, die in dieser Playlist enthalten sind.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function songs()
    {
        return $this->belongsToMany(Song::class, 'playlist_song')
        ->withTimestamps();
    }
}
