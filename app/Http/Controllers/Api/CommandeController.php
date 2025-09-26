<?php

namespace App\Http\Controllers\Api;

use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;


class CommandeController extends Controller
{
    // Liste toutes les commandes
    public function index()
    {
        $commandes = Commande::with(['product', 'customer'])->get();
        return response()->json($commandes);
    }

    // Crée une nouvelle commande
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|numeric',
            'transaction_id' => 'required|numeric',
            'status' => 'required|in:pending,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        // Générer la référence CMD001, CMD002...
        $lastCommande = Commande::orderBy('id', 'desc')->first();
        $nextId = $lastCommande ? $lastCommande->id + 1 : 1;
        $data['reference'] = 'CMD' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        $commande = Commande::create($data);

        return response()->json($commande, 201);
    }

    // Affiche une commande spécifique
    public function show($id)
    {
        $commande = Commande::with(['product', 'customer'])->find($id);

        if (!$commande) {
            return response()->json(['message' => 'Commande introuvable'], 404);
        }

        return response()->json($commande);
    }

    // Met à jour une commande
    public function update(Request $request, $id)
    {
        $commande = Commande::find($id);

        if (!$commande) {
            return response()->json(['message' => 'Commande introuvable'], 404);
        }

        $validator = Validator::make($request->all(), [
            'transaction_id' => 'sometimes|required|numeric',
            'customer_id' => 'sometimes|required|exists:users,id',
            'product_id' => 'sometimes|required|exists:products,id',
            'amount' => 'sometimes|required|numeric',
            'status' => 'sometimes|required|in:pending,completed,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $commande->update($validator->validated());

        return response()->json($commande);
    }

    // Supprime une commande
    public function destroy($id)
    {
        $commande = Commande::find($id);

        if (!$commande) {
            return response()->json(['message' => 'Commande introuvable'], 404);
        }

        $commande->delete();

        return response()->json(['message' => 'Commande supprimée avec succès']);
    }
}

