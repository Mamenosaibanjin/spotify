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
     * Die Songs, die zu dieser Playlist gehören.
     *
     * Diese Methode definiert eine n:m-Beziehung zwischen Songs und Playlisten.
     * Die Playlist kann mehrere Songs haben, und jeder Song kann in mehreren Playlisten vorkommen.
     * Die Pivot-Tabelle `playlist_song` verbindet die beiden Modelle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function songs()
    {
        return $this->belongsToMany(Song::class, 'playlist_song')
        ->withTimestamps();
    }
    
    /**
     * Die Playlisten, die zu diesem Benutzer gehören.
     *
     * Diese Methode definiert eine n:m-Beziehung zwischen Benutzern und Playlisten.
     * Der Benutzer kann mehrere Playlisten haben, und jede Playlist kann von mehreren Benutzern genutzt werden.
     * Die Pivot-Tabelle `user_playlist` verbindet die beiden Modelle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_playlist');
    }
}
