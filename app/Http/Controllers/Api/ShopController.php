<?php

namespace App\Http\Controllers\Api;

use App\Models\Shop;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $shops = Shop::with('user')->latest()->get();
        return response()->json($shops);
    }

    // Créer une boutique
    public function store(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'address' => 'required|string|max:500',
            'email' => 'required|email|max:255',
            'phone' => ['required', 'regex:/^\+?\d{8,15}$/'],
            'category' => 'required|string|in:Mode & Beauté,Électronique,Maison & Jardin,Sports & Loisirs,Alimentation,Santé & Bien-être,Automobile,Autres',
            'paymentOnDelivery' => 'required',
            'salesTax' => 'required',
            // 'isActive' => 'required|boolean',
            'template' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048', 
        ]);

        // Vérifier si la validation échoue
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public'); // stocké dans storage/app/public/logos
            $logo_url = url('storage/' . $path); // URL accessible
        }


        // Générer un slug à partir du title
        $slug = Str::slug($request->title);
        
        // Création de la boutique
        $shop = Shop::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'adresse' => $request->address,
            'mail' => $request->email,
            'phone' => $request->phone,
            'category' => $request->category,
            'paymentOnDelivery' => $request->paymentOnDelivery,
            'salesTax' => $request->salesTax,
            // 'is_active' => $request->isActive,
            'template' => $request->template,
            'logo' => $logo_url ?? null,
            'status' => 'incomplete',
            'solde' => 0,
            'lien_shop' => "shop." . $slug . env("FRONTEND_URL"),
        ]);

        return response()->json($shop, 201);
    }


    public function showPublic($slug)
    {
        $shop = Shop::where('lien_shop', $slug)->firstOrFail();
        return response()->json($shop);
    }



    // Afficher une boutique
    public function show($id)
    {
        $shop = Shop::findOrFail($id);
        return response()->json($shop);
    }

    // Mettre à jour une boutique
    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        if ($shop->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'address' => 'required|string|max:500',
            'email' => 'required|email|max:255',
            'phone' => ['required', 'regex:/^\+?\d{8,15}$/'],
            'category' => 'required|string',
            // 'template' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        $shop->update([
            'title' => $request->title,
            'description' => $request->description,
            'adresse' => $request->address,
            'mail' => $request->email,
            'phone' => $request->phone,
            'category' => $request->category,
            // 'template' => $request->template,
        ]);

        return response()->json($shop);
    }

    //Template update
    public function updateTemplate(Request $request, $id)
    {
        $shop = Shop::find($id);
        if (!$shop) {
            return response()->json(['message' => 'Boutique non trouvée'], 404);
        }

        // Stocker le template complet en JSON
        $shop->template = json_encode($request->all());

        // Stocker les champs personnalisés
        $shop->title_principal_shop = $request->title_principal_shop?? null;
        $shop->text_description_shop = $request->text_description_shop?? null;
        $shop->text_bouton_shop = $request->text_bouton_shop ?? null;

        // Stocker le style complet dans theme
        $shop->theme = json_encode($request->theme ?? []);

        $shop->update();

        return response()->json([
            'message' => 'Template mis à jour avec succès',
            'shop' => $shop
        ]);
    }


    // Desactiver une boutique
    public function deactivate($id)
    {
        $shop = Shop::findOrFail($id);

        if ($shop->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Vérifier que le statut est 'complete' avant de désactiver
        if ($shop->status !== 'complete') {
            return response()->json(['message' => 'Shop cannot be deactivated'], 400);
        }

        $shop->status = 'desactived';
        $shop->update();

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

        // Vérifier que le statut est 'desactived' avant de réactiver
        if ($shop->status !== 'desactived') {
            return response()->json(['message' => 'Shop cannot be reactivated'], 400);
        }

        $shop->status = 'complete';
        $shop->update();

        return response()->json([
            'message' => 'Shop has been reactivated',
            'shop' => $shop
        ]);
    }

}

