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
            $table->text('description')->nullable(); // Beschreibung (optional)
            $table->string('cover_path')->nullable(); // Pfad zum gespeicherten Cover-Bild
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
