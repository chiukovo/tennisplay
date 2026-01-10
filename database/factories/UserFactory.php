<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'line_user_id' => 'line_'.$this->faker->uuid(),
            'name' => $this->faker->name(),
            'line_picture_url' => null,
            'gender' => null,
            'region' => null,
            'bio' => null,
            'settings' => null,
            'uid' => 'u'.str_pad((string) $this->faker->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
        ];
    }
}


