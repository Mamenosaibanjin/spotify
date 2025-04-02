<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * TASK:
     * Ausführung der Migration:
     *      - Pivot-Tabelle 'playlist_song'
     *      - Many-to-Many-Beziehung zwischen Songs und Playlisten.
     */
    public function up(): void
    {
        Schema::create('playlist_song', function (Blueprint $table) {
            $table->id(); // Primärschlüssel (Autoincrement)
            $table->foreignId('playlist_id')->constrained()->onDelete('cascade'); // Fremdschlüssel zu Playlists
            $table->foreignId('song_id')->constrained()->onDelete('cascade'); // Fremdschlüssel zu Songs
            $table->timestamps();
        });
    }
    
    /**
     * TASK:
     * Rollback der Migration:
     *      - Löschung der Pivot-Tabelle 'playlist_song'.
     */
    public function down(): void
    {
        Schema::dropIfExists('playlist_song');
    }
};
