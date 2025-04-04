<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SpotifyApiServiceTest extends TestCase
{
    /** @test */
    public function eine_playlist_kann_mit_id_gefunden_werden()
    {
        Http::fake([
            'https://api.spotify.com/v1/playlists/*' => Http::response([
                'name' => 'Test Playlist',
                'external_urls' => ['spotify' => 'https://open.spotify.com/playlist/123'],
                'owner' => ['display_name' => 'Tester'],
                'tracks' => [],
            ], 200),
        ]);
        
        $accessToken = 'dummy_access_token';
        $playlistId = '123';
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get("https://api.spotify.com/v1/playlists/{$playlistId}");
        
        $this->assertTrue($response->successful());
        $this->assertEquals('Test Playlist', $response->json()['name']);
    }
}
