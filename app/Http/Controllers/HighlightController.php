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
            'title'  => 'required|string|max:50',
            'items'  => 'required|array|min:1',
            'items.*'=> 'integer',
        ]);

        $userId = Auth::id();
        $profileId = Auth::user()->profile_id;

        // Verifica se os stories pertencem ao usuário
        $storyIds = Story::whereIn('id', $request->items)
            ->whereProfileId($profileId)
            ->pluck('id');

        if ($storyIds->isEmpty()) {
            return response()->json(['error' => 'Nenhum story válido selecionado'], 422);
        }

        // Capa: usa a thumbnail do primeiro story selecionado
        $firstStory = Story::find($storyIds->first());
        $coverUrl = $firstStory ? url(Storage::url($firstStory->path)) : null;

        $highlight = Highlight::create([
            'user_id'   => $userId,
            'title'     => $request->title,
            'cover_url' => $coverUrl,
        ]);

        $highlight->stories()->sync($storyIds);

        return response()->json([
            'success' => true,
            'highlight' => $highlight->load('stories'),
        ]);
    }
}
