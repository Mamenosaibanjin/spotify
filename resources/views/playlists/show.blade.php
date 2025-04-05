@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Playlist Überschrift mit Cover -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="font-weight-bold">{{ $playlist->name }}</h1>
             @if($playlist->cover_path)
                                <img src="data:{{ $playlist->cover_mime }};base64,{{ $playlist->cover_path }}" 
	                                alt="Cover" 
    	                            class="img-thumbnail me-3" 
     								style="width: 120px; height: 120px; object-fit: cover; float:left;">
                            @else
                                <div class="me-3" style="width: 120px; height: 120px; background: #eee; display: flex; align-items: center; justify-content: center;">
                                    Kein Bild
                                </div>
                            @endif
        </div>
    </div>

    <!-- Anzahl der Tracks -->
    <div class="row mb-4">
        <div class="col-12">
            <h3>Tracks ({{ $tracks->total() }})</h3>
        </div>
    </div>

    <!-- Song-Suche -->
    <div class="row mb-4">
        <div class="col-12">
            <form method="GET" action="{{ route('playlists.show', $playlist->id) }}">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Nach Song suchen..." value="{{ request('search') }}">
                    <button class="btn btn-primary" type="submit">Suchen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Trackliste als Tabelle -->
    <div class="row mb-4">
        <div class="col-12">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Titel</th>
                        <th>Interpret</th>
                        <th>Dauer</th>
                        <th>Loudness</th>
                        <th>Tempo</th>
                        <th>Danceability</th>
                        <th>Album</th>
                        <th>Release Datum</th>
                        <th>Vorschau</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tracks as $track)
                        <tr>
                            <td>{{ $track->id }}</td>
                            <td>{{ $track->title }}</td>
                            <td>{{ $track->artist }}</td>
                            <td>{{ gmdate("i:s", floor($track->duration / 1000)) }}</td>
                            <td>{{ $track->audioFeature->loudness ?? 'N/A' }}</td>
                            <td>{{ $track->audioFeature->tempo ?? 'N/A' }}</td>
                            <td>{{ $track->audioFeature->danceability ?? 'N/A' }}</td>
                            <td>{{ $track->album }}</td>
                            <td>{{ \Carbon\Carbon::parse($track->release_date)->format('d.m.Y') }}</td>
                            <td>
                                <!-- Vorschau (Falls vorhanden) -->
                                @if ($track->preview_url)
                                    <audio controls>
                                        <source src="{{ $track->preview_url }}" type="audio/mpeg">
                                        Dein Browser unterstützt keine Audio-Vorschau.
                                    </audio>
                                @else
                                    Keine Vorschau verfügbar
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination (Falls es zu viele Tracks gibt) -->
    <div class="row">
        <div class="col-12">
            {{ $tracks->links() }} <!-- Paginierungslinks -->
        </div>
    </div>
</div>
@endsection
