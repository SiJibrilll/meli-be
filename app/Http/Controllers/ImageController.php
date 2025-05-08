<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    function create(Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $image = $request->file('image');
        $path = $image->store('images', 'public');

        $record = Image::create([
            'path' => $path,
        ]);

        if (!$record) {
            return response()->json([
                'message' => 'Image upload failed',
            ], 500);
        }

        return response()->json([
            'message' => 'Image uploaded successfully',
            'id' => $record->id,
        ], 200);
        
    }
}
