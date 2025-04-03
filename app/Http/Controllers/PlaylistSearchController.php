<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlaylistSearchController extends Controller
{
    /**
     * Zeigt das Suchformular und die Suchergebnisse an.
     *
     * Diese Methode überprüft, ob ein gültiges Access-Token für die Spotify-API vorhanden ist.
     * Falls ein Suchbegriff übermittelt wurde, wird eine Anfrage an die Spotify-API gesendet,
     * um Playlists zu suchen. Die Suchergebnisse werden an die View weitergegeben.
     *
     * @param Request $request Die HTTP-Anfrage mit möglichen Suchparametern.
     * @return \Illuminate\View\View
     */
    public function search(Request $request)
    {
        $playlists = [];
        
        // Token überprüfen und ggf. erneuern
        $accessToken = $this->getValidAccessToken();

        if (!$accessToken) {
            // Keine Weiterleitung zur Startseite, sondern eine Fehlermeldung anzeigen
            return view('playlists.search')->withErrors('Bitte melde dich mit deinem Spotify-Konto an, um Playlists zu suchen.');
        }
        
        // Überprüfen, ob eine Suche durchgeführt wurde
        if ($request->has('query')) {
            $query = $request->input('query'); // Der eingegebene Suchbegriff
            
            // API-Anfrage an Spotify senden
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://api.spotify.com/v1/search', [
                'q' => $query,
                'type' => 'playlist',
                'market' => 'DE',
                'limit' => 10,
            ]);
            
            // Falls das Token abgelaufen ist (401 Unauthorized), erneuern und erneut versuchen
            if ($response->status() == 401) {
                $accessToken = $this->refreshAccessToken();
                if ($accessToken) {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $accessToken,
                    ])->get('https://api.spotify.com/v1/search', [
                        'q' => $query,
                        'type' => 'playlist',
                        'market' => 'DE',
                        'limit' => 10,
                    ]);
                } else {
                    // Bei fehlgeschlagener Token-Aktualisierung eine Fehlermeldung ausgeben
                    return view('playlists.search')->withErrors('Spotify-Authentifizierung fehlgeschlagen. Bitte versuche es später erneut.');
                }
            }
            
            // Überprüfen, ob die API-Anfrage erfolgreich war
            if ($response->successful()) {
                $data = $response->json();
                $playlists = $data['playlists']['items'] ?? []; // Extrahieren der Playlist-Daten
            }
        }
        
        return view('playlists.search', compact('playlists'));
    }
    
    /**
     * Holt ein gültiges Access-Token aus der Session oder erneuert es, falls es abgelaufen ist.
     *
     * Diese Methode prüft, ob das gespeicherte Access-Token noch gültig ist.
     * Falls ja, wird es zurückgegeben. Andernfalls wird das Token mit dem Refresh-Token erneuert.
     *
     * @return string|null
     */
    private function getValidAccessToken()
    {
        $accessToken = session('spotify_access_token');
        $expiresAt = session('spotify_token_expires');
        
        // Falls das Token existiert und noch gültig ist, zurückgeben
        if ($accessToken && $expiresAt && now()->lessThan($expiresAt)) {
            return $accessToken;
        }
        
        // Andernfalls das Token erneuern
        return $this->refreshAccessToken();
    }
    
    /**
     * Holt ein neues Access-Token mit dem Refresh-Token.
     *
     * Diese Methode sendet eine Anfrage an Spotify, um ein neues Access-Token mit dem gespeicherten Refresh-Token zu erhalten.
     * Das neue Token wird in der Session gespeichert.
     *
     * @return string|null Das neue Access-Token oder null bei Fehler.
     */
    private function refreshAccessToken()
    {
        $refreshToken = session('spotify_refresh_token'); // Refresh-Token aus der Session

        if (!$refreshToken) {
            return null;
        }
        
        $clientId = config('services.spotify.client_id');
        $clientSecret = config('services.spotify.client_secret');
        
        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);
        
        if ($response->successful()) {
            $data = $response->json();
            $newAccessToken = $data['access_token'];
            
            // Speichere das neue Token in der Session
            session([
                'spotify_access_token' => $newAccessToken,
                'spotify_token_expires' => now()->addSeconds($data['expires_in']), // Neues Ablaufdatum setzen
            ]);
            
            return $newAccessToken;
        }
        
        return null;
    }
}
