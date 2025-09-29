<?php

namespace App\Http\Controllers\Api;

use App\Models\Shop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    // Lister tous les produits
    public function index()
    {
        // $products = Product::with('shop')->where('user_id', auth()->id())->latest()->get();
        $products = Product::with('shop','category')->where('user_id', auth()->id())->latest()->get();
        return response()->json($products);
    }

    // Créer un produit
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'category'    => 'required|string|max:255',
            'shop_id'     => 'required|exists:shops,id',
            'images'      => 'required|array|min:1',
            'images.*'    => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->id();

        // Vérifier si la boutique appartient à l'utilisateur connecté
        $shop = Shop::find($data['shop_id']);
        if (!$shop || $shop->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à ajouter des produits dans cette boutique'
            ], 403);
        }

        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                // stocker dans storage/app/public/products
                $path = $file->store('products', 'public');
                $images[] = url('storage/' . $path); // ✅ URL publique
            }
        }

        // La première image devient l’image principale
        $data['image'] = $images[0] ?? null;

        // Sauvegarder la galerie
        if (!empty($images)) {
            $data['images'] = json_encode($images);
        }

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Produit ajouté avec succès',
            'product' => $product
        ], 201);
    }

    // Afficher un produit
    public function show($id)
    {
        $product = Product::with('shop','category')->find($id);

        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }
        
        return response()->json($product);
    }

    public function import(Request $request, $id)
    {
        // Validation de l'ID du produit à importer et du shop cible
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
            'shop_id' => 'required|exists:shops,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Récupérer le produit à importer
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => "Produit introuvable"
            ], 404);
        }   

        // Dupliquer le produit
        $newProduct = new Product();
        $newProduct->title = $product->title;
        $newProduct->description = $product->description;
        $newProduct->price = $request->price;
        $newProduct->quantity = $product->quantity;
        $newProduct->category = $product->category;
        $newProduct->shop_id = $product->shop_id;
        $newProduct->user_id = auth()->id();
        $newProduct->image = $product->image;
        $newProduct->images = $product->images;
        $newProduct->save();

        return response()->json([
            'success' => true,
            'message' => 'Produit importé avec succès',
            'data' => $newProduct
        ], 201);
    }

    // Mettre à jour un produit
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => "Produit introuvable"
            ], 404);
        }

        // Maintenant on peut accéder à user_id en toute sécurité
        if($product->user_id !== auth()->id()){
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric',
            'qte' => 'sometimes|required|integer',
            'category_id' => 'sometimes|required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $product->update($data);

        return response()->json($product, 200);

    }

    // Supprimer un produit
    public function destroy($id)
    {
        $product = Product::find($id);
    
        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], 404);
        }
    
        if ($product->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
    
        // Vérifier si le produit a des commandes associées
        if ($product->commandes()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce produit car il a des commandes associées'
            ], 400);
        }
    
        $product->delete();
    
        return response()->json(['message' => 'Produit supprimé avec succès']);
    }
    
}
