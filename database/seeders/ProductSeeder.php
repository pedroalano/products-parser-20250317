<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $url = 'https://challenges.coode.sh/food/data/json/index.txt';
        $fileList = explode("\n", file_get_contents($url));

        foreach ($fileList as $file) {
            if (!empty($file)) {
                // Primeiro realizo download do .gz
                $gzFilePath = storage_path("app/{$file}");
                file_put_contents($gzFilePath, Http::get("https://challenges.coode.sh/food/data/json/{$file}")->body());

                // Realizo a descompressao do .gz
                $jsonFilePath = str_replace('.gz', '', $gzFilePath);
                $this->decompressGz($gzFilePath, $jsonFilePath);

                // Processo o JSON extraido dos passos anteriores
                $this->processJsonFile($jsonFilePath);

                // Limpo os arquivos
                unlink($jsonFilePath);
            }
        }
    }

    // Descompacta .dz e remove 
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

    // Processa o json extraido e limita o processamento a 100 itens
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

            $code = $product["code"];
            if (str_contains($product["code"], "\"")) {
                $code = str_replace("\"", "", $product['code']);
            }

            $batch[] = [
                'code' => $code ?? null,
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
                $batch = []; // limpeza de memoria
            }
        }

        if (!empty($batch)) {
            $this->batchInsertOrUpdate($batch);
        }


        fclose($file);
    }

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
