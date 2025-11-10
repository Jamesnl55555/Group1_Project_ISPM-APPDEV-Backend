<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Product;
use Illuminate\Validation\Rules\Exists;

class ExcelController extends Controller
{
    // public function import(Request $request){
    //     $request->validate([
    //         'excel_file' => 'required|file|mimes:xlsx,xls,csv',
    //     ]);

    //     $file = $request->file('excel_file');
    //     $spreadsheet = IOFactory::load($file->getPathname());
    //     $worksheet = $spreadsheet->getActiveSheet();
    //     $rows = $worksheet->toArray();

    //     $user = $request->user();

    //     foreach ($rows as $index => $row) {
    //         if ($index === 0) {
    //             continue;
    //         }
    //         if (empty($row[0])) {
    //             continue;
    //         }

    //         $name = trim($row[0]);
    //         $quantity = isset($row[1]) ? (int) $row[1] : 0;
    //         $price = isset($row[2]) ? (float) preg_replace('/[^\d.]/', '', $row[2]) : 0.00;
    //         $category = isset($row[3]) ? trim($row[3]) : 'Uncategorized';

    //         $product = Product::where('user_id', $user->id)
    //             ->where('name', $name)
    //             ->first();

    //         if ($product) {
    //             if ($row[3] === $product->category) {
    //             $product->quantity += (int) $row[1];
    //             $product->price = (float) $row[2];
    //             $product->save();
    //             } else {
    //             Product::create([
    //                 'user_id' => $user->id,
    //                 'name' => $row[0],
    //                 'quantity' => (int) $row[1],
    //                 'price' => (float) $row[2],
    //                 'category' => $row[3],
    //                 'is_archived' => false,
    //                 'file_path' => 'imported from excel',
    //             ]);
    //             } 
    //         }
    //         else {
    //             Product::create([
    //                 'user_id' => $user->id,
    //                 'name' => $name,
    //                 'quantity' => $quantity,
    //                 'price' => $price,
    //                 'category' => $category,
    //                 'is_archived' => false,
    //                 'file_path' => 'imported from excel',
    //             ]);
    //         }
    //     }

    //     return redirect()->route('dashboard');
    // }
}
