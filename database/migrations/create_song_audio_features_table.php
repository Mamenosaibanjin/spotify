<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * TASK:
     * Ausführung der Migration:
     *      - Tabelle 'song_audio_features'
     *      - Speicherung der Audio-Eigenschaften
     */
    public function up(): void
    {
        Schema::create('song_audio_features', function (Blueprint $table) {
            $table->id(); // Primärschlüssel (Autoincrement)
            $table->foreignId('song_id')->constrained()->onDelete('cascade'); // Verknüpfung mit einem Song
            $table->float('loudness'); // Lautstärke in Dezibel (dB)
            $table->float('tempo'); // Tempo des Songs in BPM
            $table->float('danceability'); // Tanzbarkeit (0.0 bis 1.0)
            $table->float('energy'); // Energie-Level des Songs (0.0 bis 1.0)
            $table->timestamps();
        });
    }
    
    /**
     * TASK:
     * Rollback der Migration:
     *      - Löschung der Pivot-Tabelle 'song_audio_features'.
     */
    public function down(): void
    {
        Schema::dropIfExists('song_audio_features');
    }
};
