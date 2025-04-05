@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Deine Playlisten</h1>

    <!-- Gespeicherte Playlisten -->
    <div class="card">
        <div class="card-header">
            <h2>Gespeicherte Playlisten</h2>
        </div>
        <div class="card-body">
            @if($savedPlaylists->isEmpty())  
                <p>Du hast noch keine Playlisten.</p>
            @else
                <ul class="list-group">
                    @foreach($savedPlaylists as $savedPlaylist)
                        <li class="list-group-item d-flex align-items-center">
                            @if($savedPlaylist->cover_path)
                                <img src="data:{{ $savedPlaylist->cover_mime }};base64,{{ $savedPlaylist->cover_path }}" 
	                                alt="Cover" 
    	                            class="img-thumbnail me-3" 
     								style="width: 80px; height: 80px; object-fit: cover;">
                            @else
                                <div class="me-3" style="width: 80px; height: 80px; background: #eee; display: flex; align-items: center; justify-content: center;">
                                    Kein Bild
                                </div>
                            @endif
                
                            <div>
                                <h5 class="mb-1">{{ $savedPlaylist->name }}</h5>
                                <a href="{{ route('playlists.show', ['id' => $savedPlaylist->id]) }}" class="btn btn-sm btn-outline-primary">Details anzeigen</a>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <!-- Paginierung -->
                <div class="mt-3">
                    {{ $savedPlaylists->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Hinweis und Suchformular -->
    <div class="mt-4">
        @include('playlists.search')
    </div>

</div>
@endsection
