<?php

namespace App\Http\Controllers\Api;

use App\Models\Chapitre;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class ChapitreController extends Controller
{
    /**
     * Afficher la liste des chapitres
     */
    public function index()
    {
        $chapitres = Chapitre::with('formation')->latest()->get();
        return response()->json($chapitres);
    }

    /**
     * Créer un nouveau chapitre
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'        => 'required|string|max:255',
            'lien'         => 'nullable|url',
            'ressources'   => 'nullable|string',
            'formation_id' => 'required|exists:formations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $chapitre = Chapitre::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Chapitre créé avec succès',
            'data'    => $chapitre
        ], 201);
    }

    /**
     * Afficher un chapitre
     */
    public function show($id)
    {
        $chapitre = Chapitre::with('formation')->find($id);

        if (!$chapitre) {
            return response()->json(['message' => 'Chapitre introuvable'], 404);
        }

        return response()->json($chapitre);
    }

    /**
     * Mettre à jour un chapitre
     */
    public function update(Request $request, $id)
    {
        $chapitre = Chapitre::find($id);

        if (!$chapitre) {
            return response()->json(['message' => 'Chapitre introuvable'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title'        => 'sometimes|required|string|max:255',
            'description'        => 'sometimes|required|string',
            'lien'         => 'url',
            'ressources'   => 'nullable|string',
            'formation_id' => 'sometimes|required|exists:formations,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $chapitre->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Chapitre mis à jour avec succès',
            'data'    => $chapitre
        ]);
    }

    /**
     * Supprimer un chapitre
     */
    public function destroy($id)
    {
        $chapitre = Chapitre::find($id);

        if (!$chapitre) {
            return response()->json(['message' => 'Chapitre introuvable'], 404);
        }

        $chapitre->delete();

        return response()->json(['message' => 'Chapitre supprimé avec succès']);
    }
}
