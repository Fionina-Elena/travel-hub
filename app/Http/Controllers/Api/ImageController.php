<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ImageController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        try {
            Log::info('Image upload called', ['files' => array_keys($request->allFiles())]);
            
            if (!$request->hasFile('file')) {
                return response()->json(['error' => 'No file provided'], 400);
            }

            $request->validate([
                'file' => 'required|image|max:5120',
            ]);

            $file = $request->file('file');
            $path = $file->store('tours', 'public');

            Log::info('File stored at:', ['path' => $path]);

            return response()->json([
                'url' => Storage::url($path),
                'path' => $path,
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed:', $e->errors());
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Upload error:', ['message' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
