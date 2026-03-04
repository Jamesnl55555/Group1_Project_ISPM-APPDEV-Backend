<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UpdateProfileController extends Controller
{
public function update(Request $request){
    try {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'storeName' => ['required', 'string', 'max:255'],
        ]);

        $user = request()->user();
        $user->username = $validated['username'];
        $user->storeName = $validated['storeName'];
        $user->save();
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }

    return response()->json(['success' => true]);
    }
}

