<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::latest();

        if ($request->filled('type')) {
            if ($request->type === 'image') {
                $query->where('file_type', 'like', 'image/%');
            } elseif ($request->type === 'video') {
                $query->where('file_type', 'like', 'video/%');
            } elseif ($request->type === 'document') {
                $query->where('file_type', 'like', 'application/%');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('file_name', 'like', "%{$search}%");
        }

        $media = $query->paginate(24);
        $totalCount = Media::count();
        $imageCount = Media::where('file_type', 'like', 'image/%')->count();

        return view('admin.media.index', compact('media', 'totalCount', 'imageCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,webp,svg,mp4,webm,pdf,doc,docx|max:10240',
        ]);

        $uploaded = 0;

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('media', 'public');

                Media::create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getClientMimeType(),
                    'folder' => 'general',
                ]);
                $uploaded++;
            }
        }

        return back()->with('success', "{$uploaded} file(s) uploaded successfully.");
    }

    public function destroy(Media $media)
    {
        $path = $media->file_path ?: $media->path;
        if ($path) {
            Storage::disk('public')->delete($path);
        }
        $media->delete();

        return back()->with('success', 'Media deleted successfully.');
    }
}
