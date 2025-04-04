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
                    <a href="{{ $playlist['external_urls']['spotify'] }}" target="_blank">Zur Playlist auf Spotify</a>
                @endif

                @if(isset($playlist['name']))
                    <p>{{ $playlist['name'] }}</p>
                @endif

                @if(isset($playlist['owner']['display_name']))
                    <p>Von: {{ $playlist['owner']['display_name'] }}</p>
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
                        trackListHtml += '<li>' + item.track.name + ' - ' + item.track.artists[0].name + ' (' + item.track.duration_ms / 1000 + 's)</li>';
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


    @elseif(request()->has('query'))
        <p class="mt-3">Keine Playlisten gefunden.</p>
    @endif

</div>
