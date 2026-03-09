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
        'storeName' => [ 'nullable', 'string', 'max:255'],
        'profile_image' => ['nullable','string']
    ]);

    $user = $request->user();

    $user->update([
        'name' => $validated['name'],
        'storeName' => $validated['storeName'],
        'profile_image' => $validated['profile_image'] ?? $user->profile_image
    ]);

    return response()->json([
        'success' => true,
        'user' => [
            'email' => $user->email,
            'name' => $user->name,
            'storeName' => $user->storeName,
            'profile_image' => $user->profile_image,
        ],
    ]);
    }
}

