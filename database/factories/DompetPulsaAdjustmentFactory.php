<?php

namespace Database\Factories;

use App\Models\DompetPulsa;
use Illuminate\Database\Eloquent\Factories\Factory;

class DompetPulsaAdjustmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'dompet_pulsa_id' => DompetPulsa::factory(),
            'jenis' => $this->faker->randomElement(['tambah', 'kurang']),
            'nominal' => $this->faker->randomFloat(2, 1000, 500000),
            'keterangan' => $this->faker->sentence,
            'tanggal' => $this->faker->date(),
        ];
    }
}
