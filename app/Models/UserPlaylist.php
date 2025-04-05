<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class UserPlaylist extends Model
{
    use HasFactory;
    
    protected $table = 'user_playlist';
    
    protected $fillable = ['user_id', 'playlist_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }
}
