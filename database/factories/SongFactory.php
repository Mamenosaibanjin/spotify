<?php

namespace Database\Factories;

use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

class SongFactory extends Factory
{
    protected $model = Song::class;
    
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(2),
            'spotify_id' => $this->faker->regexify('[A-Za-z0-9]{22}'),
            'artist' => $this->faker->name,
            'album' => $this->faker->words(3, true),
            'duration' => $this->faker->numberBetween(60000, 300000), // in Millisekunden
            'release_date' => $this->faker->date(),
        ];
    }
}
