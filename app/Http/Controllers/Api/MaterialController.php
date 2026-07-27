<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LearningMaterial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialController extends Controller
{
    public function download(
        Request $request,
        int $id
    ): StreamedResponse|JsonResponse {
        $material = LearningMaterial::query()
            ->with('topic:id,status,is_protected')
            ->whereHas('topic', function ($query) {
                $query->where('status', true);
            })
            ->findOrFail($id);

        $user = $request->user('sanctum');

        /*
         * Protected topic ka material login ke baghair
         * download nahi ho sakta.
         */
        if ($material->topic->is_protected && ! $user) {
            return response()->json([
                'message' => 'Please login to download this material.',
            ], 401);
        }

        /*
         * Local uploaded file.
         */
        if ($material->file_path) {
            if (! Storage::disk('public')->exists(
                $material->file_path
            )) {
                return response()->json([
                    'message' => 'Material file was not found.',
                ], 404);
            }

            $extension = pathinfo(
                $material->file_path,
                PATHINFO_EXTENSION
            );

            $fileName = Str::slug($material->title);

            if ($extension) {
                $fileName .= '.' . $extension;
            }

            return Storage::disk('public')->download(
                $material->file_path,
                $fileName
            );
        }

        /*
         * External PDF/file URL.
         *
         * Mobile app external URL ko browser ya download
         * manager mein open karegi.
         */
        if ($material->url) {
            return response()->json([
                'message' => 'External material URL retrieved successfully.',
                'data' => [
                    'id' => $material->id,
                    'title' => $material->title,
                    'type' => $material->type,
                    'download_type' => 'external',
                    'download_url' => $material->url,
                ],
            ]);
        }

        return response()->json([
            'message' => 'This material does not have a downloadable file.',
        ], 404);
    }
}
