<?php

namespace App\Http\Controllers;

use App\Models\Highlight;
use App\Story;
use Auth;
use Illuminate\Http\Request;
use Storage;

class HighlightController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'title'   => 'required|string|max:50',
            'items'   => 'required|array|min:1',
            'items.*' => 'integer',
        ]);

        $userId    = Auth::id();
        $profileId = Auth::user()->profile_id;

        $storyIds = Story::whereIn('id', $request->items)
            ->whereProfileId($profileId)
            ->pluck('id');

        if ($storyIds->isEmpty()) {
            return response()->json(['error' => 'Nenhum story válido selecionado'], 422);
        }

        $firstStory = Story::find($storyIds->first());
        $coverUrl   = $firstStory ? url(Storage::url($firstStory->path)) : null;

        $highlight = Highlight::create([
            'user_id'   => $userId,
            'title'     => $request->title,
            'cover_url' => $coverUrl,
        ]);

        $highlight->stories()->sync($storyIds);

        return response()->json([
            'success'   => true,
            'highlight' => $highlight->load('stories'),
        ]);
    }

    public function data(Request $request, $id)
    {
        $highlight = Highlight::with('stories')->findOrFail($id);
        abort_if($highlight->user_id !== Auth::id(), 403);

        return response()->json([
            'id'        => $highlight->id,
            'title'     => $highlight->title,
            'cover_url' => $highlight->cover_url,
            'story_ids' => $highlight->stories->pluck('id'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $highlight = Highlight::findOrFail($id);
        abort_if($highlight->user_id !== Auth::id(), 403);

        $request->validate([
            'title'   => 'required|string|max:50',
            'items'   => 'required|array|min:1',
            'items.*' => 'integer',
        ]);

        $profileId = Auth::user()->profile_id;

        $storyIds = Story::whereIn('id', $request->items)
            ->whereProfileId($profileId)
            ->pluck('id');

        if ($storyIds->isEmpty()) {
            return response()->json(['error' => 'Nenhum story válido selecionado'], 422);
        }

        $firstStory = Story::find($storyIds->first());
        $coverUrl   = $firstStory ? url(Storage::url($firstStory->path)) : $highlight->cover_url;

        $highlight->update([
            'title'     => $request->title,
            'cover_url' => $coverUrl,
        ]);

        $highlight->stories()->sync($storyIds);

        return response()->json([
            'success'   => true,
            'highlight' => $highlight->load('stories'),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $highlight = Highlight::findOrFail($id);
        abort_if($highlight->user_id !== Auth::id(), 403);

        // Desvincula os stories — NÃO os apaga
        $highlight->stories()->detach();
        $highlight->delete();

        return response()->json(['success' => true]);
    }

    public function view(Request $request, $id)
    {
        $highlight = Highlight::with(['stories' => function ($q) {
            $q->orderBy('highlight_story.id', 'asc');
        }])->findOrFail($id);

        $firstStory = $highlight->stories->first();

        if (! $firstStory) {
            return back()->with('error', 'Este destaque não tem stories.');
        }

        $username = $firstStory->profile->username;

        return redirect("/stories/{$username}/{$firstStory->id}");
    }
}
