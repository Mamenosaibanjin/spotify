<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Playlist;
use App\Models\PlaylistSong;
use App\Models\Song;
use App\Models\User;
use App\Models\UserPlaylist;
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
        $savedPlaylists = $user->playlists()
            ->orderByRaw('cover_path IS NULL')  // NULLs werden "größer", also ans Ende sortiert
            ->orderBy('name')                   // dann alphabetisch
            ->paginate(5);                      // Paginierte Playlists des Nutzers
        
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
    protected function getValidAccessToken()
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
        //    return null;
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
    
    /**
     * Analysiert eine Playlist anhand der Playlist-ID und gibt die Playlist-Daten sowie die Audio-Features der Tracks zurück.
     *
     * Diese Methode ruft Informationen über eine Playlist von der Spotify API ab und extrahiert die IDs der enthaltenen Tracks.
     * Anschließend wird ein weiterer API-Request an Spotify gesendet, um die Audio-Features der Tracks zu erhalten.
     * Die Methode gibt eine JSON-Antwort mit den Playlist-Daten und den Audio-Features der Tracks zurück.
     *
     * @param string $id Die ID der Playlist, die analysiert werden soll.
     * @return \Illuminate\Http\JsonResponse JSON-Antwort mit Playlist-Daten und Audio-Features oder einem Fehler.
     */
    public function analyse($id)
    {
        // Holt ein gültiges Access-Token, um auf die Spotify API zuzugreifen.
        $accessToken = $this->getValidAccessToken();
        
        // Wenn kein gültiges Access-Token vorhanden ist, gibt es eine 401-Antwort mit einer Fehlermeldung zurück.
        if (!$accessToken) {
            return response()->json(['error' => 'Kein gültiges Token'], 401);
        }
        
        // Holt sich die Playlist-Daten von der Spotify API.
        $playlistResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get("https://api.spotify.com/v1/playlists/{$id}");
        
        // Überprüft, ob die Playlist erfolgreich abgerufen wurde, andernfalls gibt es eine 404-Antwort zurück.
        if (!$playlistResponse->successful()) {
            return response()->json(['error' => 'Playlist nicht gefunden'], 404);
        }
        
        // Die Antwort von der API in ein Array umwandeln, das die Playlist-Daten enthält.
        $playlistData = $playlistResponse->json();
        
        // Extrahiert die Track-IDs aus den Playlist-Daten. Nur die IDs der Tracks werden benötigt.
        // Die Funktion `pluck('track.id')` holt sich die Track-IDs aus den jeweiligen Track-Objekten.
        // `filter()` stellt sicher, dass nur gültige IDs berücksichtigt werden, und `take(100)` begrenzt die Anzahl der IDs auf maximal 100 (Spotify-API-Grenze).
        $trackIds = collect($playlistData['tracks']['items'])
        ->pluck('track.id')
        ->filter()
        ->take(100);
        
        // Ein leeres Array für die Audio-Features der Tracks.
        $audioFeatures = [];
        
        // Wenn Track-IDs vorhanden sind, werden die Audio-Features der Tracks abgerufen.
        if ($trackIds->isNotEmpty()) {
            // Ruft die Audio-Features für die extrahierten Track-IDs von der Spotify API ab.
            $featuresResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://api.spotify.com/v1/audio-features', [
                'ids' => $trackIds->implode(','),
            ]);
            
            // Wenn die Anfrage für die Audio-Features erfolgreich war, werden die Audio-Features der Tracks in das Array $audioFeatures gespeichert.
            if ($featuresResponse->successful()) {
                $audioFeatures = $featuresResponse->json()['audio_features'];
            }
        }
        
        // Gibt eine JSON-Antwort zurück, die die Playlist-Daten und die Audio-Features enthält.
        return response()->json([
            'playlist' => $playlistData,
            'audio_features' => $audioFeatures,
        ]);
    }
    
    /**
     * Speichert eine Playlist zusammen mit ihren Songs und verknüpft sie mit einem Benutzer.
     *
     * Diese Methode überprüft, ob die Playlist bereits existiert. Falls nicht, wird sie erstellt.
     * Anschließend wird die Verbindung zwischen Benutzer und Playlist in der Tabelle `user_playlists` gespeichert.
     * Danach werden alle übermittelten Songs gespeichert, falls sie noch nicht existieren, und mit der Playlist verknüpft.
     * Die Methode erwartet, dass die Playlist-Daten, Base64-encodiertes Cover-Bild und eine Liste von Tracks im Request vorhanden sind.
     *
     * @param \Illuminate\Http\Request $request HTTP-Request mit den Feldern:
     *                                          - playlist_id (Spotify-ID der Playlist)
     *                                          - playlist_name (Name der Playlist)
     *                                          - cover_data (Base64-Daten des Covers)
     *                                          - user_id (ID des aktuellen Benutzers)
     *                                          - tracks (Array mit Track-Daten: id, title, artist, duration, album, release_date)
     * @return \Illuminate\Http\JsonResponse JSON-Antwort mit Bestätigung der Speicherung.
     */
    public function store(Request $request)
    {
        // Playlist speichern (falls noch nicht vorhanden)
        $playlist = Playlist::firstOrCreate(
            ['spotify_id' => $request->playlist_id],
            [
                'name' => $request->playlist_name,
                'cover_path' => $request->cover_data
            ]
            );
        
        // Verbindung zur User-Playlist speichern
        UserPlaylist::firstOrCreate([
            'user_id' => $request->user_id,
            'playlist_id' => $playlist->id
        ]);
        
        // Songs speichern
        foreach ($request->tracks as $track) {
            $song = Song::firstOrCreate(
                ['spotify_id' => $track['id']],
                [
                    'title' => $track['title'],
                    'artist' => $track['artist'],
                    'duration' => $track['duration'],
                    'album' => $track['album'],
                    'release_date' => !empty($track['release_date'])
                    ? date('Y-m-d', strtotime($track['release_date']))
                    : null
                ]
                );
            
            // Playlist-Song Verbindung speichern
            PlaylistSong::firstOrCreate([
                'playlist_id' => $playlist->id,
                'song_id' => $song->id
            ]);
        }
        
        return response()->json(['message' => 'Playlist gespeichert']);
    }
    
    public function show($id)
    {
        // Lade die Playlist mit den Songs und deren Audio Features
        $playlist = Playlist::with(['songs.audioFeature']) // Songs mit Audio Features laden
        ->findOrFail($id);
        
        // Filterung und Sortierung für Tracks
        $tracksQuery = $playlist->songs(); // Holt alle Songs der Playlist als Builder
        
        // Wenn ein Suchbegriff übergeben wird, filtere nach dem Titel der Songs
        if ($search = request('search')) {
            $tracksQuery->where('title', 'like', '%' . $search . '%');
        }
        
        // Sortierung der Tracks nach dem angegebenen Parameter (loudness, tempo, etc.)
        if ($sortBy = request('sort_by')) {
            $tracksQuery->orderBy($sortBy, request('sort_direction', 'asc')); // Default ist 'asc' für aufsteigend
        }
        
        // Hole die Tracks mit Paginierung
        $tracks = $tracksQuery->paginate(10); // Paginierung auf den Builder anwenden
        
        // Rückgabe an die View
        return view('playlists.show', compact('playlist', 'tracks'));
    }
    
    
}


