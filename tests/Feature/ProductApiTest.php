<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_health_check()
    {
        $response = $this->get('/api/');
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'last_cron_run', 'memory_usage']);
    }

    public function test_update_product()
    {
        $product = Product::factory()->create([
            'code' => '1234567890',
            'product_name' => 'Original Product',
            'status' => 'draft'
        ]);

        $updatedData = [
            'product_name' => 'Updated Product',
            'status' => 'published'
        ];

        $response = $this->put("/api/products/{$product->code}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'code' => '1234567890',
                'product_name' => 'Updated Product',
                'status' => 'published'
            ]);

        $this->assertDatabaseHas('products', [
            'code' => '1234567890',
            'product_name' => 'Updated Product',
            'status' => 'published'
        ]);
    }


    public function test_get_product()
    {
        $product = Product::factory()->create(['code' => '1234567890']);

        $response = $this->get("/api/products/1234567890");
        $response->assertStatus(200)
            ->assertJson(['code' => '1234567890']);
    }

    public function test_delete_product()
    {
        $product = Product::factory()->create(['code' => '1234567890']);

        $response = $this->delete("/api/products/1234567890");
        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['code' => '1234567890', 'status' => 'trash']);
    }

    public function test_product_not_found()
    {
        $response = $this->get('/api/products/9999999999');
        $response->assertStatus(404)
            ->assertJson(['error' => 'Product not found']);
    }
}
