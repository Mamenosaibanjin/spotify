<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPlaylistTable extends Migration
{
    /**
     * Führe die Migration aus.
     *
     * Diese Methode erstellt die Tabelle `user_playlist`, die als Pivot-Tabelle zwischen
     * den Benutzern und den Playlisten fungiert. Sie ermöglicht eine n:m-Beziehung zwischen
     * der User- und der Playlist-Tabelle.
     */
    public function up()
    {
        // Erstelle die Pivot-Tabelle `user_playlist`, die die Beziehungen zwischen Benutzern und Playlisten speichert.
        Schema::create('user_playlist', function (Blueprint $table) {
            $table->id(); // Erstellt eine automatische ID-Spalte als Primärschlüssel für diese Tabelle.
            
            // `user_id` wird als Fremdschlüssel zu der Tabelle `users` definiert.
            // Es wird eine referentielle Integrität sichergestellt, sodass beim Löschen eines Benutzers 
            // alle zugehörigen Einträge in dieser Pivot-Tabelle ebenfalls gelöscht werden.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // `playlist_id` wird als Fremdschlüssel zur Tabelle `playlists` definiert.
            // Beim Löschen einer Playlist werden ebenfalls alle zugehörigen Einträge in der Pivot-Tabelle 
            // durch die "cascade"-Option gelöscht.
            $table->foreignId('playlist_id')->constrained()->onDelete('cascade');
            
            // Erstellt die Zeitstempel-Spalten `created_at` und `updated_at`, die automatisch mit den jeweiligen
            // Zeitpunkten gefüllt werden, wann ein Datensatz erstellt oder aktualisiert wird.
            $table->timestamps();
        });
    }

    /**
     * Rückgängig machen der Migration.
     *
     * Diese Methode löscht die `user_playlist`-Tabelle, wenn die Migration zurückgesetzt wird.
     */
    public function down()
    {
        // Löscht die `user_playlist`-Tabelle, wenn die Migration zurückgerollt wird.
        Schema::dropIfExists('user_playlist');
    }
}
