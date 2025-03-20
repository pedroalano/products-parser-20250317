<?php

namespace App\Http\Controllers;

use App\Models\CronLog;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $lastCron = CronLog::latest('imported_at')->first();

        return response()->json([
            'status' => 'API is running',
            'last_cron_run' => $lastCron ? $lastCron->imported_at : 'Never',
            'memory_usage' => memory_get_usage(true) / 1024 / 1024 . ' MB'
        ]);
    }

    public function update(Request $request, $code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'in:draft,trash,published',
            'url' => 'nullable|url',
            'creator' => 'nullable|string',
            'created_t' => 'nullable|integer',
            'last_modified_t' => 'nullable|integer',
            'product_name' => 'nullable|string',
            'quantity' => 'nullable|string',
            'brands' => 'nullable|string',
            'categories' => 'nullable|string',
            'labels' => 'nullable|string',
            'cities' => 'nullable|string',
            'purchase_places' => 'nullable|string',
            'stores' => 'nullable|string',
            'ingredients_text' => 'nullable|string',
            'traces' => 'nullable|string',
            'serving_size' => 'nullable|string',
            'serving_quantity' => 'nullable|numeric',
            'nutriscore_score' => 'nullable|integer',
            'nutriscore_grade' => 'nullable|string',
            'main_category' => 'nullable|string',
            'image_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($request->all());

        return response()->json($product);
    }

    public function delete($code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $product->status = 'trash';

        $product->save();

        return response()->json(['message' => 'Product moved to trash']);
    }

    public function show($code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    public function list(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $products = Product::paginate($perPage);

        return response()->json($products);
    }
}
