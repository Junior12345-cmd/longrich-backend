<?php

namespace App\Http\Controllers\Api;

use App\Models\Shop;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{

    public function showBySubdomain($shop)
    {
        $shop = Shop::where('title', $shop)
                    ->firstOrFail();

        return response()->json([
            'message' => "Bienvenue dans la boutique $shop->title",
            'shop' => $shop
        ]);
    }


    // Lister toutes les boutiques
    public function index()
    {
        $shops = Shop::with('user','products')->get();
        return response()->json($shops);
    }

    // Créer une boutique
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'banner' => 'nullable|string',
            'adresse' => 'nullable|string',
            'mail' => 'nullable|email',
            'option' => 'nullable|string',
            'status' => 'nullable|string',
            'solde' => 'nullable|numeric',
            'title_principal_shop' => 'nullable|string',
            'text_description_shop' => 'nullable|string',
        ], [
            'title.unique' => 'Une boutique avec ce titre existe déjà, veuillez en choisir un autre.',
            'mail.email' => 'L’email doit être valide.',
        ]);

        // 2️⃣ Génération du slug pour le lien
        $slug = Str::slug($request->title);

        // 3️⃣ Récupération du domaine depuis .env (ou localhost)
        $domain = config('app.shop_domain', 'localhost:8000');

        // 4️⃣ Génération automatique du lien
        $lienShop = "{$slug}.{$domain}";

        // 5️⃣ Création de la boutique
        $shop = Shop::create(array_merge(
            $request->all(),
            [
                'user_id' => Auth::id(),
                'lien_shop' => $lienShop,
            ]
        ));

        // 6️⃣ Retour JSON avec status et lien
        return response()->json([
            "message" => "Boutique créée avec succès !",
            'shop' => $shop,
            'lien_shop' => $shop->lien_shop
        ], 201);
    }


    // Afficher une boutique
    public function show($id)
    {
        $shop = Shop::with('products')->findOrFail($id);
        return response()->json($shop);
    }

    // Mettre à jour une boutique
    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);
        $this->authorize('update', $shop);

        $shop->update($request->all());
        return response()->json($shop);
    }

    // Supprimer une boutique
    public function deactivate($id)
    {
        $shop = Shop::findOrFail($id);
        if ($shop->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $shop->status = 'inactive';
        $shop->save();

        return response()->json([
            'message' => 'Shop has been deactivated',
            'shop' => $shop
        ]);
    }

    public function reactivate($id)
{
    $shop = Shop::findOrFail($id);

    if ($shop->user_id !== Auth::id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $shop->update(['status' => 'active']);

    return response()->json([
        'message' => 'Shop has been reactivated',
        'shop' => $shop
    ]);
}

}

