<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Wenn der Benutzer bereits bei Spotify authentifiziert ist, leite ihn auf das Dashboard weiter
        if (session()->has('spotify_access_token')) {
            return redirect()->route('playlists.search');
        }
        
        // Wenn der Benutzer noch nicht bei Spotify authentifiziert ist, leite zur Spotify-Login-Seite weiter
        return redirect('/auth/spotify');
    }
}