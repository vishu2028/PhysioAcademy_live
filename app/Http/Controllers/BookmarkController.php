<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Topic;
use App\Models\LearningMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required|in:Topic,LearningMaterial',
        ]);

        $user = Auth::user();
        $modelClass = $request->type === 'Topic' ? Topic::class : LearningMaterial::class;
        $item = $modelClass::findOrFail($request->id);

        $bookmark = Bookmark::where([
            'user_id' => $user->id,
            'bookmarkable_id' => $item->id,
            'bookmarkable_type' => $modelClass,
        ])->first();

        if ($bookmark) {
            $bookmark->delete();
            return response()->json([
                'status' => 'removed',
                'message' => 'Bookmark removed successfully.',
                'isBookmarked' => false
            ]);
        } else {
            Bookmark::create([
                'user_id' => $user->id,
                'bookmarkable_id' => $item->id,
                'bookmarkable_type' => $modelClass,
            ]);
            return response()->json([
                'status' => 'added',
                'message' => 'Bookmark added successfully.',
                'isBookmarked' => true
            ]);
        }
    }

    public function remove(Request $request, $id)
    {
        $bookmark = Bookmark::where('user_id', Auth::id())->where('id', $id)->firstOrFail();
        $bookmark->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Removed from bookmarks']);
        }

        return back()->with('success', 'Bookmark removed successfully.');
    }
}
