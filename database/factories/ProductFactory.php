<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->ean13,
            'status' => 'draft',
            'imported_t' => now(),
            'url' => $this->faker->url,
            'creator' => $this->faker->name,
            'created_t' => now()->timestamp,
            'last_modified_t' => now()->timestamp,
            'product_name' => $this->faker->word,
            'quantity' => $this->faker->randomNumber(2) . ' units',
            'brands' => $this->faker->company,
            'categories' => $this->faker->word,
            'labels' => $this->faker->word,
            'cities' => $this->faker->city,
            'purchase_places' => $this->faker->city,
            'stores' => $this->faker->company,
            'ingredients_text' => $this->faker->sentence,
            'traces' => $this->faker->word,
            'serving_size' => '100g',
            'serving_quantity' => $this->faker->randomFloat(2, 0, 500),
            'nutriscore_score' => $this->faker->numberBetween(-15, 40),
            'nutriscore_grade' => $this->faker->randomElement(['a', 'b', 'c', 'd', 'e']),
            'main_category' => $this->faker->word,
            'image_url' => $this->faker->imageUrl,
        ];
    }
}
