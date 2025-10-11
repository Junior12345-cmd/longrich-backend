<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Commande;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class CommandeController extends Controller
{
    // Liste toutes les commandes
    public function index($shopId)
    {
        $commandes = Commande::where('orderable_type', "App\Models\Product")
            ->whereHas('orderable', function ($query) use ($shopId) {
                $query->where('shop_id', $shopId);
            })
            ->with('orderable')
            ->latest()->get();

        return response()->json($commandes);
    }


    public function updateStatus($id, Request $request)
    {
        $order = Commande::find($id);

        if (!$order) {
            return response()->json(['message' => 'Commande introuvable'], 404);
        }

        // Valider le statut
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'order' => $order
        ]);
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
            'amount' => 'required',
            'quantity' => 'required|integer|min:1',
            'transaction_id' => 'nullable|string',
            'status' => 'required|in:pending,completed,canceled',
        ], [
            // Messages de validation
            'required' => 'Le champ :attribute est requis.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'max' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'integer' => 'Le champ :attribute doit être un entier.',
            'min' => 'Le champ :attribute doit être au moins :min.',
            'exists' => 'Le :attribute sélectionné est invalide.',
            'in' => 'Le :attribute sélectionné est invalide.',
            'email' => "Le champ :attribute doit être une adresse e-mail valide.",
            'url' => "Le champ :attribute doit être une URL valide."
        ]);
        
        // Définir des noms lisibles pour chaque champ
        $validator->setAttributeNames([
            'customer.name' => 'Nom',
            'customer.email' => 'Email',
            'customer.phone' => 'Téléphone',
            'customer.address' => 'Adresse',
            'customer.neighborhood' => 'Quartier',
            'customer.city' => 'Ville',
            'customer.country' => 'Pays',
            'customer.geolocation' => 'Géolocalisation',
            'product_id' => 'Produit',
            'amount' => 'Montant',
            'quantity' => 'Quantité',
            'transaction_id' => 'ID de transaction',
            'status' => 'Statut de la commande',
        ]);
        
        // Vérifier les erreurs
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }        

        $data = $validator->validated();

        // Générer la référence CMD001, CMD002...
        $reference = 'CMD' . date('YmdHis') . strtoupper(Str::random(4));

        $product = Product::find($data['product_id']);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Produit introuvable'], 404);
        }
        
        $newQuantity = ((int) $product->quantity) - $data['quantity'];
        if ($newQuantity < 0) {
            return response()->json(['success' => false, 'message' => 'Quantité insuffisante en stock'], 400);
        }
        
        $product->update(['quantity' => $newQuantity]);
        

        // Créer la commande
        $commande = Commande::create([
            'customer' => json_encode($data['customer'], JSON_UNESCAPED_UNICODE),
            'orderable_id' => $data['product_id'],
            'orderable_type' => 'App\Models\Product',
            'amount' => (float) $data['amount'],
            'quantity' => $data['quantity'],
            'transaction_id' => $data['transaction_id'] ?? null,
            'status' => $data['status'],
            'reference' => $reference,
        ]);

        // Renvoyer une réponse JSON propre
        return response()->json([
            'success' => true,
            'commande' => [
                'id' => $commande->id,
                'reference' => $commande->reference,
                'amount' => $commande->amount,
                'quantity' => $commande->quantity,
                'status' => $commande->status,
                'customer' => json_decode($commande->customer, true),
            ]
        ], 201);
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

