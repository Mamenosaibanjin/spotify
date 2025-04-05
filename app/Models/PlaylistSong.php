<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistSong extends Model
{
    use HasFactory;
    
    protected $table = 'playlist_song';
    
    protected $fillable = ['playlist_id', 'song_id'];
    
    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }
    
    public function song()
    {
        return $this->belongsTo(Song::class);
    }
}
