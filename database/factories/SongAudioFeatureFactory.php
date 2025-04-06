<?php

namespace Database\Factories;

use App\Models\Song;
use App\Models\SongAudioFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

class SongAudioFeatureFactory extends Factory
{
    protected $model = SongAudioFeature::class;
    
    public function definition(): array
    {
        return [
            'song_id' => Song::factory(), // oder $this->faker->randomDigitNotNull wenn du Song vorher erstellst
            'loudness' => $this->faker->randomFloat(2, 0, 180), // in dB
            'tempo' => $this->faker->randomFloat(2, 60, 200),   // BPM
            'danceability' => $this->faker->randomFloat(2, 0, 1), // 0.0 – 1.0
            'energy' => $this->faker->randomFloat(2, 0, 1), // 0.0 – 1.0
        ];
    }
}
