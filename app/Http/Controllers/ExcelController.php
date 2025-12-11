<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Product;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;

class ExcelController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $file = $request->file('excel_file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $user = $request->user();

        $cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key' => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET')
            ]
        ]);

        $uploadApi = new UploadApi();

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Skip header row
            if (empty($row[0])) continue;

            $name = trim($row[0]);
            $quantity = isset($row[1]) ? (int) $row[1] : 0;
            $price = isset($row[2]) ? (float) preg_replace('/[^\d.]/', '', $row[2]) : 0.00;
            $category = isset($row[3]) ? trim($row[3]) : 'Uncategorized';
            $imageLink = isset($row[4]) ? trim($row[4]) : null;

            // Default to 'empty'
            $cloudinaryUrl = 'empty';

            if ($imageLink) {
                // Convert Google Drive share links to direct links
                if (str_contains($imageLink, 'drive.google.com') && preg_match('/\/d\/(.*?)\//', $imageLink, $matches)) {
                    $fileId = $matches[1];
                    $imageLink = "https://drive.google.com/uc?export=view&id={$fileId}";
                }

                // Attempt Cloudinary upload
                try {
                    $uploadResult = $uploadApi->upload($imageLink, [
                        'folder' => 'products',
                        'resource_type' => 'image',
                    ]);
                    $cloudinaryUrl = $uploadResult['secure_url'] ?? 'empty';
                } catch (\Exception $e) {
                    Log::error("Cloudinary upload failed for row {$index}: " . $e->getMessage());
                    $cloudinaryUrl = 'empty';
                }
            }

            // Check if product already exists
            $product = Product::where('user_id', $user->id)
                ->where('name', $name)
                ->first();

            if ($product) {
                if ($category === $product->category) {
                    $product->quantity += $quantity;
                    $product->price = $price;
                    $product->file_path = $cloudinaryUrl;
                    $product->save();
                } else {
                    Product::create([
                        'user_id' => $user->id,
                        'name' => $name,
                        'quantity' => $quantity,
                        'price' => $price,
                        'category' => $category,
                        'is_archived' => false,
                        'file_path' => $cloudinaryUrl,
                    ]);
                }
            } else {
                Product::create([
                    'user_id' => $user->id,
                    'name' => $name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'category' => $category,
                    'is_archived' => false,
                    'file_path' => $cloudinaryUrl,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Products imported successfully.',
        ]);
    }
}
