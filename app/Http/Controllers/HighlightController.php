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

        // Verifica se os stories pertencem ao usuário
        $storyIds = Story::whereIn('id', $request->items)
            ->whereProfileId($profileId)
            ->pluck('id');

        if ($storyIds->isEmpty()) {
            return response()->json(['error' => 'Nenhum story válido selecionado'], 422);
        }

        // Capa: usa a thumbnail do primeiro story selecionado
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

        // Capa: sempre o primeiro story selecionado
        $firstStory = Story::find($storyIds->first());
        $coverUrl   = $firstStory ? url(Storage::url($firstStory->path)) : $highlight->cover_url;

        $highlight->update([
            'title'     => $request->title,
            'cover_url' => $coverUrl,
        ]);

        // Substitui os stories do destaque pelos novos selecionados
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

        // Remove apenas o vínculo na tabela pivot — NÃO apaga os stories
        $highlight->stories()->detach();

        // Apaga o destaque
        $highlight->delete();

        return response()->json(['success' => true]);
    }
}
