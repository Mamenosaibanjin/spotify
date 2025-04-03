@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Deine Playlisten</h1>

    <!-- Überprüfen, ob der Benutzer Playlisten hat -->
    @if($playlists->isEmpty())  <!-- Falls keine Playlisten vorhanden sind -->
        <p>Du hast noch keine Playlisten.</p>
    @else
        <!-- Anzeige der Playlisten in einer Liste -->
        <ul class="list-group">
            @foreach($playlists as $playlist)
                <li class="list-group-item">
                    {{ $playlist->name }} <!-- Der Name der Playlist wird angezeigt -->
                </li>
            @endforeach
        </ul>

        <!-- Paginierung: Zeigt Navigationslinks an, um durch die Playlisten-Seiten zu navigieren -->
        <div class="mt-3">
            {{ $playlists->links() }}  <!-- Laravel-Links für die Paginierung (next/prev) -->
        </div>
    @endif
</div>
@endsection
