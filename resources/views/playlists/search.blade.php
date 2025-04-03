@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Playlist-Suche</h1>

    <!-- Suchformular -->
    <form action="{{ route('playlists.search') }}" method="GET">
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
                        @if(isset($playlist['external_urls']['spotify']))
                            <a href="{{ $playlist['external_urls']['spotify'] }}" target="_blank">Zur Playlist auf Spotify</a>
                        @endif
        
                        @if(isset($playlist['name']))
                            <p>{{ $playlist['name'] }}</p>
                        @endif
        
                        @if(isset($playlist['owner']['display_name']))
                            <p>Von: {{ $playlist['owner']['display_name'] }}</p>
                        @endif
                    </li>
                @endif
            @endforeach
        </ul>

    @elseif(request()->has('query'))
        <p class="mt-3">Keine Playlisten gefunden.</p>
    @endif
</div>
@endsection
