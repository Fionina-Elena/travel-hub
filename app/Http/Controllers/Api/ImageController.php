<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store('tours', 'public');

        return response()->json([
            'url' => Storage::url($path),
            'path' => $path,
        ]);
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        Storage::disk('public')->delete($request->path);

        return response()->json(['success' => true]);
    }
}
