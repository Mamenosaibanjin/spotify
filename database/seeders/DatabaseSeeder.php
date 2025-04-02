<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Song;
use App\Models\Playlist;
use App\Models\SongAudioFeature;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Erstellt zwei Beispiel-Songs
        $song1 = Song::create([
            'title' => 'Song A',
            'artist' => 'Artist 1',
            'album' => 'Album X',
            'duration' => 210, // 3 Minuten 30 Sekunden
            'release_date' => '2022-01-01'
        ]);
        
        $song2 = Song::create([
            'title' => 'Song B',
            'artist' => 'Artist 2',
            'album' => 'Album Y',
            'duration' => 185, // 3 Minuten 5 Sekunden
            'release_date' => '2023-03-15'
        ]);
        
        // Erstellt eine Beispiel-Playlist
        $playlist = Playlist::create([
            'name' => 'Meine Playlist',
            'description' => 'Eine tolle Playlist mit meinen Lieblingssongs',
            'cover_path' => 'covers/playlist1.jpg' // Beispiel-Pfad für das Cover
        ]);
        
        // Verknüpft die Songs mit der Playlist
        $playlist->songs()->attach([$song1->id, $song2->id]);
        
        // Erstellt Audio-Feature-Daten für Song A
        SongAudioFeature::create([
            'song_id' => $song1->id,
            'loudness' => -5.2, // Dezibel
            'tempo' => 120.5, // BPM
            'danceability' => 0.85, // Tanzbarkeit
            'energy' => 0.9 // Energie-Level
        ]);
    }
    
    
}
