<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\CronLog;
use App\Models\User;
use App\Notifications\ImportFailedNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ImportProducts extends Command
{
    protected $signature = 'products:import';
    protected $description = 'Import products from Open Food Facts using CRON';

    public function handle()
    {
        $startTime = Carbon::now();
        $this->info('Starting product import...');

        try {
            // Fetch the index file
            $url = 'https://challenges.coode.sh/food/data/json/index.txt';
            $fileList = explode("\n", file_get_contents($url));

            foreach ($fileList as $file) {
                if (!empty($file)) {
                    $this->info("Processing file: {$file}");

                    // Download and store .gz file
                    $gzFilePath = storage_path("app/{$file}");
                    file_put_contents($gzFilePath, Http::get("https://challenges.coode.sh/food/data/json/{$file}")->body());

                    // Decompress the .gz file
                    $jsonFilePath = str_replace('.gz', '', $gzFilePath);
                    $this->decompressGz($gzFilePath, $jsonFilePath);

                    // Process and import the data
                    $this->processJsonFile($jsonFilePath);

                    // Clean up files
                    unlink($jsonFilePath);
                }
            }

            // Log success
            CronLog::create([
                'imported_at' => $startTime,
                'status' => 'success',
                'details' => 'Product import completed successfully.'
            ]);

            $this->info('Import completed successfully!');
        } catch (\Exception $e) {
            Log::error("Product import failed: " . $e->getMessage(), ['exception' => $e]);

            // Log failure
            CronLog::create([
                'imported_at' => $startTime,
                'status' => 'failure',
                'details' => $e->getMessage(),
            ]);

            $user = User::where('email', "johndoe@example.com")->first();
            if ($user) {
                $user->notify(new ImportFailedNotification($e->getMessage()));
            }

            $this->error("Import failed: " . $e->getMessage());
        }
    }

    // Decompress .gz file
    private function decompressGz($gzFilePath, $jsonFilePath)
    {
        $gz = gzopen($gzFilePath, 'rb');
        $jsonFile = fopen($jsonFilePath, 'wb');

        while (!gzeof($gz)) {
            fwrite($jsonFile, gzread($gz, 4096));
        }

        gzclose($gz);
        fclose($jsonFile);
        unlink($gzFilePath);
    }

    // Process JSON data with limit
    private function processJsonFile($jsonFilePath, $limit = 100)
    {
        $batch = [];
        $batchSize = 50;
        $processedCount = 0;

        $file = fopen($jsonFilePath, 'r');
        while (!feof($file) && $processedCount < $limit) {
            $line = fgets($file);
            if (empty(trim($line))) continue;

            $product = json_decode($line, true);
            if (!$product) continue;

            $code = $product["code"] ?? null;
            $code = str_contains($code, "\"") ? str_replace("\"", "", $code) : $code;

            $batch[] = [
                'code' => $code,
                'status' => $product['status'] ?? 'draft',
                'imported_t' => now(),
                'url' => $product['url'] ?? '',
                'creator' => $product['creator'] ?? '',
                'created_t' => $product['created_t'] ?? null,
                'last_modified_t' => $product['last_modified_t'] ?? null,
                'product_name' => $product['product_name'] ?? '',
                'quantity' => $product['quantity'] ?? '',
                'brands' => $product['brands'] ?? '',
                'categories' => $product['categories'] ?? '',
                'labels' => $product['labels'] ?? '',
                'cities' => $product['cities'] ?? '',
                'purchase_places' => $product['purchase_places'] ?? '',
                'stores' => $product['stores'] ?? '',
                'ingredients_text' => $product['ingredients_text'] ?? '',
                'traces' => $product['traces'] ?? '',
                'serving_size' => $product['serving_size'] ?? '',
                'serving_quantity' => $product['serving_quantity'] ?? null,
                'nutriscore_score' => $product['nutriscore_score'] ?? null,
                'nutriscore_grade' => $product['nutriscore_grade'] ?? '',
                'main_category' => $product['main_category'] ?? '',
                'image_url' => $product['image_url'] ?? '',
            ];

            $processedCount++;

            if (count($batch) >= $batchSize || $processedCount >= $limit) {
                $this->batchInsertOrUpdate($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->batchInsertOrUpdate($batch);
        }

        fclose($file);
    }

    // Insert or Update using upsert
    private function batchInsertOrUpdate(array $batch)
    {
        DB::table('products')->upsert(
            $batch,
            ['code'],
            [
                'status',
                'imported_t',
                'url',
                'creator',
                'created_t',
                'last_modified_t',
                'product_name',
                'quantity',
                'brands',
                'categories',
                'labels',
                'cities',
                'purchase_places',
                'stores',
                'ingredients_text',
                'traces',
                'serving_size',
                'serving_quantity',
                'nutriscore_score',
                'nutriscore_grade',
                'main_category',
                'image_url'
            ]
        );
    }
}
