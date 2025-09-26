<?php

namespace App\Http\Controllers\Api;

use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class CommandeController extends Controller
{
    /**
     * Lister toutes les commandes
     */
    public function index()
    {
        $orders = Order::with('orderable', 'user')->get();
        return response()->json($orders);
    }

    /**
     * Créer une commande
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderable_id'   => 'required|integer',
            'orderable_type' => 'required|string|in:App\\Models\\Product,App\\Models\\Formation,App\\Models\\Live',
            'amount'         => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Génération de la référence unique
        $reference = 'CMD' . str_pad(Order::count() + 1, 3, '0', STR_PAD_LEFT);

        $order = Order::create([
            'user_id'        => auth()->id(),
            'orderable_id'   => $request->orderable_id,
            'orderable_type' => $request->orderable_type,
            'amount'         => $request->amount,
            'status'         => 'pending',
            'reference'      => $reference,
        ]);

        return response()->json($order, 201);
    }

    /**
     * Détails d'une commande
     */
    public function show($id)
    {
        $order = Order::with('orderable', 'user')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Commande non trouvée'], 404);
        }

        return response()->json($order);
    }

    /**
     * Mise à jour d'une commande
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Commande non trouvée'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|required|string|in:pending,paid,cancelled',
            'amount' => 'sometimes|required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $order->update($validator->validated());

        return response()->json($order);
    }

    /**
     * Supprimer une commande
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Commande non trouvée'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Commande supprimée']);
    }
}
