<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SpotifyAuthenticated
{
    /**
     * Überprüft, ob der Benutzer Spotify-authentifiziert ist.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Wenn der Access Token nicht in der Session ist, den Benutzer zur Spotify-Login-Seite weiterleiten
        if (!session('spotify_access_token')) {
            return redirect('/auth/spotify');
        }
        
        return $next($request);
    }
}
