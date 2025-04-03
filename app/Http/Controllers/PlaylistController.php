<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class PlaylistController extends Controller
{
    /**
     * Zeigt die paginierten Playlisten des eingeloggten Benutzers an.
     *
     * Diese Methode ruft alle Playlisten des aktuell angemeldeten Benutzers ab.
     * Es wird eine Paginierung verwendet, um sicherzustellen, dass nur eine
     * begrenzte Anzahl von Playlisten pro Seite angezeigt wird (10 Playlisten pro Seite).
     * Die Ergebnisse werden dann an den View übergeben, um die Playlisten anzuzeigen.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Abrufen der Playlisten des aktuell angemeldeten Benutzers
        $user = auth()->user(); // Holt den aktuell angemeldeten Benutzer
        $playlists = $user->playlists()->paginate(10);  // Paginierung: Zeigt 10 Playlisten pro Seite
        
        // Rückgabe der Playlisten zur Anzeige im View
        return view('playlists.index', compact('playlists'));  // Übergibt die Playlisten an die View
    }
}
