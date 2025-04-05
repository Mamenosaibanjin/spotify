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
            @if(isset($playlist['external_urls']['spotify']) || isset($playlist['name']) || isset($playlist['owner']['display_name']))
                <li class="list-group-item">
                    @if(isset($playlist['external_urls']['spotify']))
                        (Playlist) ID: {{ $playlist['id'] }}
                    @endif
    
                    @if(isset($playlist['name']))
                        <p>(Playlist) Name: {{ $playlist['name'] }}</p>
                    @endif
    
                    @if(isset($playlist['images'][0]['url']))
                        @php
                            $coverUrl = $playlist['images'][0]['url'];
                            $coverData = base64_encode(file_get_contents($coverUrl));
                        @endphp
                        <p>(Playlist) Cover: {{ $coverData }}</p>
                    @endif
    
                    <!-- Button, um die Analyse zu laden -->
                    <button class="btn btn-info analyse-playlist" data-playlist-id="{{ $playlist['id'] }}">Analyse anzeigen</button>
    
                    <!-- Slide-Down Bereich für die Analyse -->
                    <div class="playlist-analysis" id="analysis-{{ $playlist['id'] }}" style="display:none;">
                        <div class="analysis-header">
                            <p>{{ $playlist['description'] ?? 'Keine Beschreibung verfügbar.' }}</p>
                        </div>
                        <div class="analysis-tracks">
                            <h4>Tracks</h4>
                            <ul id="track-list-{{ $playlist['id'] }}"></ul>
                            <!-- Speichern-Button am Ende der Track-Liste -->
   							<button
                                class="btn btn-success save-playlist"
                                data-playlist-id="{{ $playlist['id'] }}"
                                data-playlist-name="{{ $playlist['name'] }}"
                                data-cover="{{ $coverData }}"
                            >Speichern</button>
                        </div>
    
                    </div>
                </li>
            @endif
        @endforeach
		</ul>

        <!-- jQuery und das Script für die Slide-Down Logik -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
        $(document).ready(function() {
            // Klick-Event für den Analyse-Button
            $('.analyse-playlist').click(function() {
                var playlistId = $(this).data('playlist-id');
                var analysisDiv = $('#analysis-' + playlistId);
        
                // Slide-Down Effekt
                analysisDiv.slideToggle();
        
                // Überprüfen, ob die Analyse bereits geladen wurde
                if (!analysisDiv.data('loaded')) {
                    // API-Aufruf zur Playlist-Analyse
                    $.get('{{ url("/playlists/") }}/' + playlistId + '/analyse', function(data) {
                        if (data.playlist && data.audio_features) {
                            // Playlist-Details anzeigen
                            var trackListHtml = '';
                            data.playlist.tracks.items.forEach(function(item) {
                                trackListHtml += '<li class="list-group-item" ' +
                                    'data-track-id="' + item.track.id + '" ' +
                                    'data-track-title="' + item.track.name + '" ' +
                                    'data-track-artist="' + item.track.artists[0].name + '" ' +
                                    'data-track-duration="' + item.track.duration_ms + '" ' +
                                    'data-track-album="' + item.track.album.name + '" ' +
                                    'data-track-release-date="' + item.track.album.release_date + '">' +
                                    item.track.name + ' – ' + item.track.artists[0].name +
                                    '</li>';
                            });
                            $('#track-list-' + playlistId).html(trackListHtml);
        
                            // Audio-Features anzeigen
                            var audioFeaturesHtml = '';
                            data.audio_features.forEach(function(feature) {
                                audioFeaturesHtml += '<li>' + feature.loudness + ' - ' + feature.tempo + ' - ' + feature.danceability + ' - ' + feature.energy + '</li>';
                            });
                            $('#audio-features-' + playlistId).html(audioFeaturesHtml);
        
                            // Markiere die Analyse als geladen
                            analysisDiv.data('loaded', true);
                        }
                    });
                }
            });
        });
        </script>

        <script>
        $(document).ready(function() {
            // Klick-Event für den Speichern-Button
            $('.save-playlist').click(function() {
                var playlistId = $(this).data('playlist-id');
                var playlistName = $(this).data('playlist-name');
                var coverData = $(this).data('cover');
                var userId = {{ auth()->id() }}; // Aktuell eingeloggter User
        
                var trackData = [];
                $('#track-list-' + playlistId).find('li').each(function() {
                    trackData.push({
                        id: $(this).data('track-id'),
                        title: $(this).data('track-title'),
                        artist: $(this).data('track-artist'),
                        duration: $(this).data('track-duration'),
                        album: $(this).data('track-album'),
                        release_date: $(this).data('track-release-date')
                    });
                    console.log('trackData');
                    console.log(trackData);
                });
        
                // AJAX-POST-Anfrage an Laravel-Route
                $.ajax({
                    url: '{{ route("playlists.store") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        playlist_id: playlistId,
                        playlist_name: playlistName,
                        cover_data: coverData,
                        user_id: userId,
                        tracks: trackData
                    },
                    success: function(response) {
                        alert('Playlist erfolgreich gespeichert!');
                    },
                    error: function(xhr) {
                        alert('Fehler beim Speichern: ' + xhr.responseText);
                    }
                });
            });
        });
        </script>


    @elseif(request()->has('query'))
        <p class="mt-3">Keine Playlisten gefunden.</p>
    @endif

</div>
