<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class PlaylistController extends Controller
{
    /**
     * Zeigt die Playlisten des Nutzers und verarbeitet Suchanfragen.
     *
     * Falls keine Suchanfrage (`query`) gestellt wurde, werden nur die gespeicherten Playlists des Nutzers angezeigt.
     * Falls eine Suchanfrage vorhanden ist, wird die Spotify API verwendet, um Playlists basierend auf name, ID oder URL zu suchen.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $savedPlaylists = $user->playlists()->paginate(10); // Paginierte Playlists des Nutzers
        
        $query = $request->query('query');
        $playlists = [];
        
        if ($query) {
            // Suche in der Spotify API, falls eine Suchanfrage existiert
            $playlists = $this->searchSpotifyPlaylists($query);
        }
        
        return view('playlists.index', compact('savedPlaylists', 'playlists'));
    }
    
    /**
     * Durchsucht die Spotify API nach Playlists basierend auf einer Suchanfrage.
     *
     * Unterstützt:
     * - Suche nach Playlist-Name
     * - Suche nach Playlist-ID (22-stellige alphanumerische Zeichenfolge)
     * - Suche nach Playlist-URL (die Playlist-ID wird aus der URL extrahiert)
     *
     * @param string $query Die Suchanfrage (Name, ID oder URL).
     * @return array Ein Array mit den gefundenen Playlists oder ein leeres Array bei Fehlern.
     */
    private function searchSpotifyPlaylists($query)
    {
        $accessToken = $this->getValidAccessToken();
        
        if (!$accessToken) {
            return [];
        }
        
        $response = null;
        
        if (filter_var($query, FILTER_VALIDATE_URL)) {
            // Suche nach Playlist-URL
            $playlistId = basename($query);
            $response = $this->searchById($accessToken, $playlistId);
        } elseif (strlen($query) == 22 && ctype_alnum($query)) {
            // Falls eine 22-stellige alphanumerische Zeichenfolge eingegeben wurde, teste zuerst die ID-Suche
            $response = $this->searchById($accessToken, $query);
            
            // Falls die ID-Suche fehlschlägt, nach Name suchen
            if ($response->failed()) {
                $response = $this->searchByName($accessToken, $query);
            }
        } else {
            // Standard-Suche nach Namen
            $response = $this->searchByName($accessToken, $query);
        }
        
        if ($response && $response->successful()) {
            $data = $response->json();
            
            // Falls die Suche nur eine einzelne Playlist zurückgibt (ID-Suche)
            if (isset($data['name'])) {
                return [$data];
            }
            
            return $data['playlists']['items'] ?? [];
        }
        
        return [];
    }
    
    /**
     * Sucht eine Playlist anhand ihrer ID über die Spotify API.
     *
     * @param string $accessToken Das gültige Spotify Access-Token
     * @param string $playlistId Die eindeutige ID der Playlist
     * @return \Illuminate\Http\Client\Response Die API-Antwort mit den Playlist-Datem
     */
    private function searchById($accessToken, $playlistId)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get("https://api.spotify.com/v1/playlists/{$playlistId}", [
            'market' => 'DE',
            'fields' => 'name,external_urls,owner,tracks',
        ]);
    }
    
    /**
     * Sucht Playlists nach Namen über die Spotify API.
     *
     * @param string $accessToken Das gültige Spotify Access-Token
     * @param string $query Der Suchbegriff für die Playlist-Suche
     * @return \Illuminate\Http\Client\Response Die API-Antwort mit den Playlist-Datem
     */
    private function searchByName($accessToken, $query)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get('https://api.spotify.com/v1/search', [
            'q' => $query,
            'type' => 'playlist',
            'market' => 'DE',
            'limit' => 10,
        ]);
    }
    
    /**
     * Holt ein gültiges Access-Token aus der Session oder erneuert es, falls es abgelaufen ist.
     *
     * Falls ein gültiges Token existiert, wird es zurückgegeben.
     * Andernfalls wird das Token mit dem gespeicherten Refresh-Token erneuert.
     *
     * @return string|null Gibt das gültige Access-Token zurück oder `null`, falls kein Token verfügbar ist.
     */
    private function getValidAccessToken()
    {
        $accessToken = session('spotify_access_token');
        $expiresAt = session('spotify_token_expires');
        
        if ($accessToken && $expiresAt && now()->lessThan($expiresAt)) {
            return $accessToken;
        }
        
        return $this->refreshAccessToken();
    }
    
    /**
     * Erneuert das Spotify Access-Token mit dem gespeicherten Refresh-Token.
     *
     * Falls das Refresh-Token vorhanden ist, wird eine Anfrage an die Spotify API gesendet,
     * um ein neues Access-Token zu erhalten. Das neue Token wird in der Session gespeichert.
     *
     * @return string|null Das neue Access-Token oder `null`, falls die Erneuerung fehlschlägt.
     */
    private function refreshAccessToken()
    {
        $refreshToken = session('spotify_refresh_token');
        
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
            
            session([
                'spotify_access_token' => $newAccessToken,
                'spotify_token_expires' => now()->addSeconds($data['expires_in']),
            ]);
            
            return $newAccessToken;
        }
        
        return null;
    }
}
