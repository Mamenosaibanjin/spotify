<div class="container">
    <h1>Playlist-Suche</h1>

    <!-- Hinweisbox mit den möglichen Suchoptionen -->
    <div class="alert alert-info">
        <strong>Hinweis:</strong> Du kannst Playlists auf verschiedene Arten suchen:
        <ul>
            <li><strong>Nach Name:</strong> Gib den Namen einer Playlist ein (z. B. "Schlager Party 2025").</li>
            <li><strong>Nach Playlist-ID:</strong> Falls du die ID kennst (22-stellig, z. B. "1J2vYkrZaRh9mPoRAiVVmU").</li>
            <li><strong>Nach Playlist-URL:</strong> Kopiere die URL einer Playlist (z. B. <code>https://open.spotify.com/playlist/1J2vYkrZaRh9mPoRAiVVmU</code>).</li>
        </ul>
    </div>
    
    <!-- Suchformular -->
    <form action="{{ route('playlists.index') }}" method="GET">
        <div class="mb-3">
            <label for="query" class="form-label">Playlist suchen:</label>
            <input type="text" id="query" name="query" class="form-control" value="{{ request('query') }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Suchen</button>
    </form>

    <!-- Suchergebnisse -->
    @if(!empty($playlists))
        <h2 class="mt-4">Gefundene Playlisten</h2>
        <ul class="list-group">
        @foreach($playlists as $playlist)
            @if(
                isset($playlist['external_urls']['spotify']) || 
                isset($playlist['name']) || 
                isset($playlist['owner']['display_name'])
            )
            <li class="list-group-item">
                {{-- Cover-Bild --}}
                @if(isset($playlist['images'][0]['url']))
                    <img src="{{ $playlist['images'][0]['url'] }}" alt="Playlist-Cover" width="150">
                @endif
    
                {{-- Titel --}}
                <h5>{{ $playlist['name'] ?? 'Kein Titel vorhanden' }}</h5>
    
                {{-- Playlist-ID --}}
                <p><strong>ID:</strong> {{ $playlist['id'] ?? 'Unbekannt' }}</p>
    
                {{-- Beschreibung --}}
                @if(!empty($playlist['description']))
                    <p><strong>Beschreibung:</strong> {!! $playlist['description'] !!}</p>
                @endif
    
                {{-- Eigentümer --}}
                @if(isset($playlist['owner']['display_name']))
                    <p><strong>Von:</strong> {{ $playlist['owner']['display_name'] }}</p>
                @endif
    
                {{-- Spotify-Link --}}
                @if(isset($playlist['external_urls']['spotify']))
                    <a href="{{ $playlist['external_urls']['spotify'] }}" target="_blank">Zur Playlist auf Spotify</a>
                @endif
    

            </li>
            @endif
        @endforeach
    </ul>

    @elseif(request()->has('query'))
        <p class="mt-3">Keine Playlisten gefunden.</p>
    @endif

</div>
