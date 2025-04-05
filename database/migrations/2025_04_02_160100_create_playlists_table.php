<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * TASK:
     * Ausführung der Migration:
     *      - Tabelle 'playlists'
     */
    public function up(): void
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->id(); // Primärschlüssel (Auto-Inkrement)
            $table->string('name'); // Name der Playlist
            $table->string('spotify_id'); // Spotify-ID
            $table->longText('cover_path')->nullable(); // Cover-Bild in Base64
            $table->timestamps();
        });
    }
    
    /**
     * TASK:
     * Rollback der Migration:
     *      - Löschung der Tabelle 'playlists'.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlists');
    }
};
