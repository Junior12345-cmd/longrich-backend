<?php

namespace App\Http\Controllers\API;

use App\Models\Formation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;

class FormationController extends Controller
{
    // Lister toutes les formations avec leurs chapitres
    public function index()
    {
        $formations = Formation::with('chapitres')->get();
        return response()->json($formations);
    }

    // Créer une nouvelle formation
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric',
            'format'      => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // On récupère les données validées
        $data = $validator->validated();

        // On ajoute manuellement les champs calculés
        $data['user_id'] = auth()->id();
        $data['status']  = 'draft';

        // Création de la formation
        $formation = Formation::create($data);

        return response()->json($formation, 201);
    }


    // Afficher une formation spécifique
    public function show($id)
    {
        $formation = Formation::with('chapitres')->find($id);

        if (!$formation) {
            return response()->json(['message' => 'Formation introuvable'], 404);
        }

        return response()->json($formation);
    }

    // Mettre à jour une formation
    public function update(Request $request, $id)
    {
        $formation = Formation::find($id);

        if (!$formation) {
            return response()->json(['message' => 'Formation introuvable'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric',
            'format' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $formation->update($validator->validated());

        return response()->json($formation);
    }

    // Supprimer une formation
    public function destroy($id)
    {
        $formation = Formation::find($id);

        if (!$formation) {
            return response()->json(['message' => 'Formation introuvable'], 404);
        }

        // Supprimer les chapitres associés avant de supprimer la formation
        $formation->chapitres()->delete();
        $formation->delete();

        return response()->json(['message' => 'Formation supprimée avec succès']);
    }
}
