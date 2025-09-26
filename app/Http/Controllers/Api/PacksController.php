<?php

namespace App\Http\Controllers\Api;

use App\Models\Packs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;

class PacksController extends Controller
{
    // Lister tous les packs
    public function index()
    {
        $packs = Packs::with('country')->get();
        return response()->json([   
            "packs"=>$packs,
            "total"=>count($packs)
        ]);
    }

    // Créer un pack
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'country_id' => 'required|exists:countries,id',
            'prix' => 'required|numeric',
            'features' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pack = Packs::create($validator->validated());

        return response()->json($pack, 201);
    }

    // Afficher un pack spécifique
    public function show($id)
    {
        $pack = Packs::with('country')->find($id);

        if (!$pack) {
            return response()->json(['message' => 'Pack introuvable'], 404);
        }

        return response()->json($pack);
    }

    // Mettre à jour un pack
    public function update(Request $request, $id)
    {
        $pack = Packs::find($id);

        if (!$pack) {
            return response()->json(['message' => 'Pack introuvable'], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'country_id' => 'sometimes|required|exists:countries,id',
            'prix' => 'sometimes|required|numeric',
            'features' => 'nullable|array',
            'status' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pack->update($validator->validated());

        return response()->json($pack);
    }

    // Supprimer un pack
    public function destroy($id)
    {
        $pack = Packs::find($id);

        if (!$pack) {
            return response()->json(['message' => 'Pack introuvable'], 404);
        }

        $pack->delete();

        return response()->json(['message' => 'Pack supprimé avec succès']);
    }
}
