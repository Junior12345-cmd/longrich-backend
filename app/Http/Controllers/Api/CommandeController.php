<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Commande;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class CommandeController extends Controller
{
    // Liste toutes les commandes
    public function index($shopId)
    {
        // Récupérer toutes les commandes pour des produits
        $commandes = Commande::where('orderable_type', "App\Models\Product")
        ->with('orderable')
        ->get();

        return response()->json($commandes);
    }
    
    public function store_commande_produit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer.name' => 'required|string|max:255',
            'customer.email' => 'nullable|email|max:255',
            'customer.phone' => 'required|string|max:50',
            'customer.address' => 'required|string|max:255',
            'customer.neighborhood' => 'nullable|string|max:255',
            'customer.city' => 'required|string|max:255',
            'customer.country' => 'required|string|max:255',
            'customer.geolocation' => 'nullable|url',
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|numeric',
            'transaction_id' => 'nullable|string',
            'status' => 'required|in:pending,completed,canceled',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $data = $validator->validated();
    
        // Générer la référence CMD001, CMD002...
        $lastCommande = Commande::orderBy('id', 'desc')->first();
        $nextId = $lastCommande ? $lastCommande->id + 1 : 1;
        $reference = 'CMD' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        
        $commande = Commande::create([
            'customer' => json_encode($request->input('customer')),
            'orderable_id' => $request->product_id,
            'orderable_type' => 'App\Models\Product',
            'amount' => $request->amount,
            'transaction_id' => $request->transaction_id,
            'status' => $request->status,
            'reference' => 'CMD' . str_pad((Commande::max('id') ?? 0) + 1, 3, '0', STR_PAD_LEFT),
        ]);
        
    
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

