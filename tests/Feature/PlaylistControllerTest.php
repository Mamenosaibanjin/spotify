<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Playlist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlaylistControllerTest extends TestCase
{
    
    /** @test */
    public function ein_unauthentifizierter_benutzer_wird_umgeleitet()
    {
        $response = $this->get(route('playlists.index'));
        
        $response->assertRedirect(route('login'));
    }
    
    /** @test */
    public function ein_eingeloggter_benutzer_sieht_seine_playlisten()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Füge hier ggf. Dummy-Playlists hinzu, falls du ein Modell hast
        
        $response = $this->get(route('playlists.index'));
        
        $response->assertStatus(200);
        $response->assertViewIs('playlists.index');
        $response->assertViewHas('savedPlaylists');
    }
    
    /** @test */
    public function eine_suchanfrage_ohne_token_liefert_keine_ergebnisse()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Leere Session → kein Token
        $response = $this->get(route('playlists.index', ['query' => 'Rock']));
        
        $response->assertStatus(200);
        $response->assertViewHas('playlists', []);
    }
    
    /** @test */
    public function nur_die_eigenen_playlisten_werden_angezeigt()
    {
        $benutzer = User::factory()->create();
        $fremderBenutzer = User::factory()->create();
        
        // Playlisten erstellen
        $playlist = Playlist::factory()->create(['name' => 'Meine Playlist', 'spotify_id' => '123456789ABCDEFGHIJKLM']);
        $fremdePlaylist = Playlist::factory()->create(['name' => 'Fremde Playlist', 'spotify_id' => 'ABCDEFGHIJKLM123456789']);
        
        // Benutzer zuweisen über Pivot
        $benutzer->playlists()->attach($playlist);
        $fremderBenutzer->playlists()->attach($fremdePlaylist);
        
        $this->actingAs($benutzer)
        ->get(route('playlists.index'))
        ->assertSee('Meine Playlist')
        ->assertDontSee('Fremde Playlist');
    }
    
    /** @test */
    public function playlisten_werden_nach_cover_und_name_sortiert()
    {
        $benutzer = User::factory()->create();
        
        $playlistMitCover = Playlist::factory()->create([
            'name' => 'Apfel',
            'spotify_id' => 'ABCDEFGHIJKLM123456789',
            'cover_path' => '/9j/4AAQSkZJRgABAQAAAQABAAD/...' // vollständiger Test-Base64-String
        ]);
        
        $playlistOhneCover = Playlist::factory()->create([
            'name' => 'Zebra',
            'spotify_id' => '123456789ABCDEFGHIJKLM',
            'cover_path' => null
        ]);
        
        $playlistInDerMitte = Playlist::factory()->create([
            'name' => 'Banane',
            'spotify_id' => 'ABCDEFGH9876543210',
            'cover_path' => '/9j/4AAQSkZJRgABAQAAAQABAAD/...' // nochmal ein Dummy
        ]);
        
        // Playlists dem Benutzer zuweisen
        $benutzer->playlists()->attach([
            $playlistMitCover->id,
            $playlistOhneCover->id,
            $playlistInDerMitte->id
        ]);
        
        $antwort = $this->actingAs($benutzer)->get(route('playlists.index'));
        
        $antwort->assertSeeInOrder([
            'Apfel',
            'Banane',
            'Zebra'
        ]);
    }
    
    
    /** @test */
    public function playlist_und_songs_werden_gespeichert()
    {
        $benutzer = User::factory()->create();
        
        $daten = [
            'user_id' => $benutzer->id,
            'playlist_id' => 'spotify123',
            'playlist_name' => 'Meine Test-Playlist',
            'cover_data' => '/9j/4AAQSkZJRgABAQAAAQABAAD/...', // vollständiger Test-Base64-String
            'tracks' => [
                [
                    'id' => 'track123',
                    'title' => 'Lied A',
                    'artist' => 'Künstler A',
                    'album' => 'Album A',
                    'duration' => 180000,
                    'release_date' => '2020-01-01',
                ]
            ]
        ];
        
        $this->actingAs($benutzer)
        ->postJson(route('playlists.store'), $daten)
        ->assertStatus(200)
        ->assertJson(['message' => 'Playlist gespeichert']);
        
        $this->assertDatabaseHas('playlists', ['spotify_id' => 'spotify123']);
        $this->assertDatabaseHas('songs', ['spotify_id' => 'track123']);
        $this->assertDatabaseHas('playlist_song', []); // Keine konkreten Werte notwendig
    }
    
    /** @test */
    public function analyse_gibt_unauthorized_wenn_token_fehlt()
    {
        $controllerMock = $this
        ->partialMock(\App\Http\Controllers\PlaylistController::class)
        ->shouldAllowMockingProtectedMethods()
        ->shouldReceive('getValidAccessToken')
        ->andReturn(null)
        ->getMock();
            
            $antwort = $controllerMock->analyse('abc123');
            $this->assertEquals(401, $antwort->getStatusCode());
    }
    
    /** @test */
    public function gespeicherte_playlists_werden_aus_den_suchergebnissen_entfernt()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Gespeicherte Playlist simulieren
        $playlist = Playlist::factory()->create([
            'spotify_id' => 'spotify123',
            'name' => 'Bereits gespeichert'
        ]);
        $user->playlists()->attach($playlist);
        
        // Session mit Token simulieren
        session(['spotify_access_token' => 'testtoken']);
        
        // Fake-Antworten von Spotify simulieren (hier z. B. direkt im Controller als Testhilfe)
        $this->mock(\App\Http\Controllers\PlaylistController::class, function ($mock) use ($playlist) {
            $mock->shouldAllowMockingProtectedMethods()
            ->makePartial()
            ->shouldReceive('searchSpotifyPlaylists')
            ->andReturn([
                ['id' => 'spotify123', 'name' => 'Bereits gespeichert'],
                ['id' => 'spotify456', 'name' => 'Neue Playlist']
            ]);
        });
            
            $response = $this->get(route('playlists.index', ['query' => 'Test']));
            
            $response->assertStatus(200);
            $response->assertViewHas('playlists', function ($playlists) {
                return count($playlists) === 1 && $playlists[0]['id'] === 'spotify456';
            });
    }
    
    /** @test */
    public function suchergebnisse_werden_nicht_gefiltert_wenn_nichts_gespeichert()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        session(['spotify_access_token' => 'testtoken']);
        
        $this->mock(\App\Http\Controllers\PlaylistController::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods()
            ->makePartial()
            ->shouldReceive('searchSpotifyPlaylists')
            ->andReturn([
                ['id' => 'spotify999', 'name' => 'Unbekannte Playlist'],
            ]);
        });
            
            $response = $this->get(route('playlists.index', ['query' => 'Chill']));
            
            $response->assertStatus(200);
            $response->assertViewHas('playlists', function ($playlists) {
                return count($playlists) === 1 && $playlists[0]['id'] === 'spotify999';
            });
    }
    
    /** @test */
    public function playlist_wird_nicht_faelschlich_aus_suchergebnissen_entfernt()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Gespeicherte Playlist mit ID 'spotify123'
        $gespeichertePlaylist = Playlist::factory()->create([
            'spotify_id' => 'spotify123',
            'name' => 'Gespeichert'
        ]);
        $user->playlists()->attach($gespeichertePlaylist);
        
        // Spotify liefert 'spotify1234' zurück → darf **nicht** gefiltert werden
        session(['spotify_access_token' => 'testtoken']);
        
        $this->mock(\App\Http\Controllers\PlaylistController::class, function ($mock) {
            $mock->shouldAllowMockingProtectedMethods()
            ->makePartial()
            ->shouldReceive('searchSpotifyPlaylists')
            ->andReturn([
                ['id' => 'spotify1234', 'name' => 'Fast gleich'],
                ['id' => 'spotify123', 'name' => 'Exakt gleich'], // sollte raus
            ]);
        });
            
            $response = $this->get(route('playlists.index', ['query' => 'Test']));
            
            $response->assertStatus(200);
            $response->assertViewHas('playlists', function ($playlists) {
                // Nur 'spotify1234' darf übrig bleiben
                return count($playlists) === 1 && $playlists[0]['id'] === 'spotify1234';
            });
    }
    
}
