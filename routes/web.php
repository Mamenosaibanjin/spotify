<?php 
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\PlaylistController;
use App\Http\Controllers\PlaylistSearchController;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Route für den Spotify-Login
Route::get('/auth/spotify', function () {
    $clientId = env('SPOTIFY_CLIENT_ID');
    $redirectUri = env('SPOTIFY_REDIRECT_URI');
    $scope = 'user-library-read playlist-read-private'; // Beispielhafte Scopes
    $responseType = 'code'; // Authorization Code Flow

    $url = "https://accounts.spotify.com/authorize?client_id={$clientId}&redirect_uri={$redirectUri}&scope={$scope}&response_type={$responseType}";

    return redirect($url); // Weiterleitung zur Spotify-Login-Seite
});

// Callback-Route für Spotify
Route::get('/auth/callback', function (Request $request) {
    $code = $request->query('code'); // Der Code, der von Spotify zurückgegeben wurde

    if (!$code) {
        return response()->json(['error' => 'Authorization code missing'], 400);
    }

    // Anfrage an Spotify, um das Access Token zu erhalten
    $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => env('SPOTIFY_REDIRECT_URI'),
        'client_id' => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
    ]);

    if ($response->successful()) {
        $data = $response->json();
        $accessToken = $data['access_token'];
        $refreshToken = $data['refresh_token'];
        $expiresIn = $data['expires_in'];

        // Speichern der Tokens in der Session
        session([
            'spotify_access_token' => $accessToken,
            'spotify_refresh_token' => $refreshToken,
            'spotify_token_expires' => now()->addSeconds($expiresIn),
        ]);

        // Weiterleitung zur Playlist-Suche oder Dashboard
        return redirect()->route('playlists.search');
    } else {
        return response()->json(['error' => 'Unable to fetch access token'], 500);
    }
});

// Playlist-Routen nach Login (normaler Auth)
Route::middleware('auth')->group(function () {
    Route::get('/playlists', [PlaylistController::class, 'index'])->name('playlists.index');
    Route::get('/playlists/search', [PlaylistSearchController::class, 'search'])->name('playlists.search');
});
