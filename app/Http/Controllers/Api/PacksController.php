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
        $packs = Packs::with('country')->latest()->get();
        return response()->json([   
            "packs"=>$packs,
            "total"=>count($packs)
        ]);
        // $packs = Pack::with('country')->latest()->get()->map(function($pack) {
        //     return [
        //         'id' => $pack->id,
        //         'name' => $pack->title,
        //         'price' => number_format($pack->price, 0, ',', ' ') . ' FCFA',
        //         'features' => json_decode($pack->features, true),
        //         'country' => $pack->country->title
        //     ];
        // });
    
        return response()->json($packs);
    }

    public function indexSearch(Request $request)
    {
        $countryId = $request->query('country_id');
        $query = Packs::where('status', 'actived');
        if ($countryId) {
            $query->where('country_id', $countryId);
        }
        $packs = $query->get();
        // \Log::info('Packs fetched:', ['country_id' => $countryId, 'packs' => $packs]);
        return response()->json($packs);
    }

    // Créer un pack
    public function store(Request $request)
    {
        // Créer le validateur
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
            'title'      => 'required|string|max:255',
            'description'=> 'required|string',
            'price'      => 'required|integer',
            'features'   => 'nullable|array',
            'status'     => 'nullable|string|in:pending,completed,cancelled',
        ]);

        // Vérifier si la validation échoue
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Création du pack
        $pack = Packs::create($validator->validated());

        // Retourner la réponse JSON
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
            'price' => 'sometimes|required|numeric',
            'features' => 'nullable|array',
            'status' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pack->update($validator->validated());

        return response()->json($pack);
    }

    public function changeStatus($id)
    {
        $pack = Packs::find($id);

        if (!$pack) {
            return response()->json(['message' => 'Pack introuvable'], 404);
        }

        // Basculer le statut
        $pack->status = $pack->status === 'actived' ? 'inactive' : 'actived';
        $pack->save();

        return response()->json([
            'message' => 'Statut du pack mis à jour',
            'status' => $pack->status
        ]);
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
