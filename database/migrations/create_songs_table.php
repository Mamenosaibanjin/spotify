<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * TASK:
     * Ausführung der Migration:
     *      - Tabelle 'songs'
     */
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id(); // Primärschlüssel (Auto-Inkrement)
            $table->string('title'); // Titel des Songs
            $table->string('artist'); // Name des Künstlers/Bands
            $table->string('album')->nullable(); // Name des Albums (optional)
            $table->integer('duration'); // Dauer des Songs in Sekunden
            $table->date('release_date')->nullable(); // Veröffentlichungsdatum (optional)
            $table->timestamps();
        });
    }
    
    /**
     * TASK:
     * Rollback der Migration:
     *      - Löschung der Tabelle 'songs'.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
