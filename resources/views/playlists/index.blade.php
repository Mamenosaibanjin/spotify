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
                        <li class="list-group-item">
                            {{ $savedPlaylist->name }}
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
