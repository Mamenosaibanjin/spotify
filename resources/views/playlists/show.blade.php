@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Playlist-Überschrift mit Cover -->
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

    <!-- Filterformular -->
    <form method="GET" action="{{ route('playlists.show', $playlist->id) }}" class="mb-4">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="search">Titel oder Interpret</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Suchen..." value="{{ request('search') }}">
            </div>

            <!-- Dauer Filter -->
            <div class="col-md-6 mb-3">
                <label>Dauer (in Sekunden)</label>
                <div class="d-flex">
                    <input type="number" name="duration_min" class="form-control me-2" placeholder="Min." value="{{ request('duration_min') }}" min="0" max="3600">
                    <input type="number" name="duration_max" class="form-control" placeholder="Max." value="{{ request('duration_max') }}" min="0" max="3600">
                </div>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="exact_match_duration" id="exact_match_duration" {{ request('exact_match_duration') ? 'checked' : '' }}>
                    <label class="form-check-label" for="exact_match_duration">Exakter Wert</label>
                </div>
            </div>
            
            <!-- Loudness Filter -->
            <div class="col-md-6 mb-3">
                <label>Loudness (dB)</label>
                <div class="d-flex">
                    <input type="number" step="0.1" name="loudness_min" class="form-control me-2" placeholder="Min." value="{{ request('loudness_min') }}" min="-20" max="20">
                    <input type="number" step="0.1" name="loudness_max" class="form-control" placeholder="Max." value="{{ request('loudness_max') }}" min="-20" max="20">
                </div>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="exact_match_loudness" id="exact_match_loudness" {{ request('exact_match_loudness') ? 'checked' : '' }}>
                    <label class="form-check-label" for="exact_match_loudness">Exakter Wert</label>
                </div>
            </div>
            
            <!-- Tempo Filter -->
            <div class="col-md-6 mb-3">
                <label>Tempo (BPM)</label>
                <div class="d-flex">
                    <input type="number" name="tempo_min" class="form-control me-2" placeholder="Min." value="{{ request('tempo_min') }}" min="0" max="300">
                    <input type="number" name="tempo_max" class="form-control" placeholder="Max." value="{{ request('tempo_max') }}" min="0" max="300">
                </div>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="exact_match_tempo" id="exact_match_tempo" {{ request('exact_match_tempo') ? 'checked' : '' }}>
                    <label class="form-check-label" for="exact_match_tempo">Exakter Wert</label>
                </div>
            </div>
            
            <!-- Danceability Filter -->
            <div class="col-md-6 mb-3">
                <label>Danceability (0.0 - 1.0)</label>
                <div class="d-flex">
                    <input type="number" step="0.01" name="danceability_min" class="form-control me-2" placeholder="Min." value="{{ request('danceability_min') }}" min="0" max="1">
                    <input type="number" step="0.01" name="danceability_max" class="form-control" placeholder="Max." value="{{ request('danceability_max') }}" min="0" max="1">
                </div>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="exact_match_danceability" id="exact_match_danceability" {{ request('exact_match_danceability') ? 'checked' : '' }}>
                    <label class="form-check-label" for="exact_match_danceability">Exakter Wert</label>
                </div>
            </div>


            <!-- Danceability Filter -->
            <div class="col-md-6 mb-3">
                <label>Danceability (0.0 - 1.0)</label>
                <div class="d-flex">
                    <input type="number" step="0.01" name="danceability_min" class="form-control me-2" placeholder="Min." value="{{ request('danceability_min') }}">
                    <input type="number" step="0.01" name="danceability_max" class="form-control" placeholder="Max." value="{{ request('danceability_max') }}">
                </div>
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="exact_match_danceability" id="exact_match_danceability" {{ request('exact_match_danceability') ? 'checked' : '' }}>
                    <label class="form-check-label" for="exact_match_danceability">Exakter Wert</label>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="sort">Sortierung</label>
                <select name="sort" id="sort" class="form-control">
                    <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>Titel (aufsteigend)</option>
                    <option value="title_desc" {{ request('sort') == 'title_desc' ? 'selected' : '' }}>Titel (absteigend)</option>
                    <option value="artist_asc" {{ request('sort') == 'artist_asc' ? 'selected' : '' }}>Interpret (aufsteigend)</option>
                    <option value="artist_desc" {{ request('sort') == 'artist_desc' ? 'selected' : '' }}>Interpret (absteigend)</option>
                    <option value="duration_asc" {{ request('sort') == 'duration_asc' ? 'selected' : '' }}>Dauer (aufsteigend)</option>
                    <option value="duration_desc" {{ request('sort') == 'duration_desc' ? 'selected' : '' }}>Dauer (absteigend)</option>
                    <option value="loudness_asc" {{ request('sort') == 'loudness_asc' ? 'selected' : '' }}>Loudness (aufsteigend)</option>
                    <option value="loudness_desc" {{ request('sort') == 'loudness_desc' ? 'selected' : '' }}>Loudness (absteigend)</option>
                    <option value="tempo_asc" {{ request('sort') == 'tempo_asc' ? 'selected' : '' }}>Tempo (aufsteigend)</option>
                    <option value="tempo_desc" {{ request('sort') == 'tempo_desc' ? 'selected' : '' }}>Tempo (absteigend)</option>
                    <option value="danceability_asc" {{ request('sort') == 'danceability_asc' ? 'selected' : '' }}>Danceability (aufsteigend)</option>
                    <option value="danceability_desc" {{ request('sort') == 'danceability_desc' ? 'selected' : '' }}>Danceability (absteigend)</option>
                	<option value="album_asc" {{ request('sort') == 'album_asc' ? 'selected' : '' }}>Album (aufsteigend)</option>
                    <option value="album_desc" {{ request('sort') == 'album_desc' ? 'selected' : '' }}>Album (absteigend)</option>
                    <option value="release_date_asc" {{ request('sort') == 'release_date_asc' ? 'selected' : '' }}>Release Datum (aufsteigend)</option>
                    <option value="release_date_desc" {{ request('sort') == 'release_date_desc' ? 'selected' : '' }}>Release Datum (absteigend)</option>
    			</select>
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary" type="submit">Filtern</button>
            <a href="{{ route('playlists.show', $playlist->id) }}" class="btn btn-secondary">Zurücksetzen</a>
        </div>
    </form>

    <!-- Trackliste -->
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
                    @forelse ($tracks as $track)
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
                                @if ($track->preview_url)
                                    <audio controls>
                                        <source src="{{ $track->preview_url }}" type="audio/mpeg">
                                        Dein Browser unterstützt keine Audio-Vorschau.
                                    </audio>
                                @else
                                    Keine Vorschau
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Keine Songs gefunden.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="row">
        <div class="col-12">
            {{ $tracks->links() }}
        </div>
    </div>
</div>
@endsection
