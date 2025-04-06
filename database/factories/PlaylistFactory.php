<?php

namespace Database\Factories;

use App\Models\Playlist;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaylistFactory extends Factory
{
    protected $model = Playlist::class;
    
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'spotify_id' => $this->faker->regexify('[A-Za-z0-9]{22}'),
            'cover_path' => $this->faker->boolean(50) ? '/9j/4AAQSkZJRgABAQAAAQABAAD/4gIcSUNDX1BST0ZJTEUAAQEAAAIMbGNtcwIQAAB...' : null, // 50% Chance für Cover
        ];
    }
}
