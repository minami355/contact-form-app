<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $faker = \Faker\Factory::create('ja_JP');

        return [
            'category_id' => null,
            'first_name' => $faker->firstName(),
            'last_name' => $faker->lastName(),
            'gender' => $faker->randomElement([1, 2, 3]),
            'email' => $faker->unique()->safeEmail(),
            'tel' => $faker->numerify('090########'),
            'address' => $faker->address(),
            'building' => $faker->secondaryAddress(),
            'detail' => $faker->realText(100),
        ];
    }
}