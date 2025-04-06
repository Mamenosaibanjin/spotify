<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Playlist;
use App\Models\Song;
use App\Models\SongAudioFeature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;


class PlaylistShowTest extends TestCase
{
    //use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Test-User anlegen & einloggen
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Playlist & Song vorbereiten
        $this->playlist = Playlist::factory()->create();
        
        $this->song = Song::factory()->create([
            'title' => 'Test Song',
            'artist' => 'Test Artist',
            'duration' => 180000, // 3 Minuten
            'album' => 'Test Album',
            'release_date' => '2020-01-01',
        ]);
        
        $this->playlist->songs()->attach($this->song);
        
        SongAudioFeature::factory()->create([
            'song_id' => $this->song->id,
            'loudness' => -10.0,
            'tempo' => 120,
            'danceability' => 0.75,
        ]);
    }
    
    /** @test */
    public function gefiltert_nach_exakter_dauer()
    {
        $response = $this->get("/playlists/{$this->playlist->id}?duration_min=180&exact_match_duration=on");
        
        $response->assertStatus(200);
        $response->assertSeeText('Test Song');
    }
    
    /** @test */
    public function gefiltert_nach_danceability_range()
    {
        $response = $this->get("/playlists/{$this->playlist->id}?danceability_min=0.7&danceability_max=0.8");
        
        $response->assertStatus(200);
        $response->assertSeeText('Test Song');
    }
    
    /** @test */
    public function gefiltert_nach_exakter_loudness()
    {
        $response = $this->get("/playlists/{$this->playlist->id}?loudness_min=-10&exact_match_loudness=on");
        
        $response->assertStatus(200);
        $response->assertSeeText('Test Song');
    }
    
    /** @test */
    public function sortiert_nach_titel_absteigend()
    {
        $response = $this->get("/playlists/{$this->playlist->id}?sort=title_desc");
        
        $response->assertStatus(200);
        $response->assertSeeText('Test Song');
    }
    
    /** @test */
    public function kombiniert_suche_und_sortierung()
    {
        $response = $this->get("/playlists/{$this->playlist->id}?search=Test&sort=duration_desc");
        
        $response->assertStatus(200);
        $response->assertSeeText('Test Song');
    }
    
    /** @test */
    public function ergebnisse_paginieren()
    {
        Song::factory()->count(20)->create()->each(function ($song) {
            $this->playlist->songs()->attach($song);
            SongAudioFeature::factory()->create(['song_id' => $song->id]);
        });
            
            $response = $this->get("/playlists/{$this->playlist->id}");
            
            $response->assertStatus(200);
            $response->assertSee('class="pagination"', false);
    }
}
