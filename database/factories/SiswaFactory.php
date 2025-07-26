<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\spp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Siswa>
 */
class SiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'nisn' => $this->faker->unique()->numberBetween(100000, 999999),
            // 'nis' => $this->faker->unique()->numberBetween(100000, 999999),
            // 'nama' => fake()->name,
            // 'kelas_id' => Kelas::inRandomOrder()->first()->id,
            // 'alamat' => fake()->address,
            // 'no_hp' => fake()->phoneNumber,

        ];
    }
}
