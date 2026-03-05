<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UpdateProfileController extends Controller
{
    public function update(Request $request)
    {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'storeName' => ['required', 'string', 'max:255'],
    ]);

    $user = $request->user();

    $user->name = $validated['name'];
    $user->storeName = $validated['storeName'];
    $user->save();

    return response()->json([
        'success' => true,
        'user' => [
            'email' => $user->email,
            'name' => $user->name,
            'storeName' => $user->storeName,
        ],
    ]);
    }
}

