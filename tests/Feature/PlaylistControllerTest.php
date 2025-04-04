<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlaylistControllerTest extends TestCase
{
    use RefreshDatabase;
    
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
    
    // Weitere Tests mit Token-Mocking möglich (siehe unten)
}
