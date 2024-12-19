<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $title = ucfirst(fake()->unique()->word()),
            'slug' => str($title)->slug(),
            'body' => fake()->sentences(6, true),
        ];
    }
}
