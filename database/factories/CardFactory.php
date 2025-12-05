<?php

namespace Database\Factories;

use App\Enums\CardBrandEnum;
use App\Enums\CardStatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'balance' => 0,
            'status' => $this->faker->randomElement([CardStatusEnum::Ativo, CardStatusEnum::Bloqueado, CardStatusEnum::Cancelado]),
            'brand' => $this->faker->randomelement([CardBrandEnum::MasterCard, CardBrandEnum::Visa, CardBrandEnum::AmericanExpress, CardBrandEnum::Elo]),
        ];
    }
}
