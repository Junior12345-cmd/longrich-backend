<?php

namespace App\Http\Controllers\Api;

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
            'title' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
            'shop_id' => 'required|exists:shops,id',
            // 'shop_id' => [
            //     'required',
            //     Rule::exists('shops', 'id')->where(function ($query) {
            //         $query->where('status', 'completed');
            //     })
            // ],
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'images' => 'nullable|array',
            // 'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
    
        $data = $validator->validated();
        $data['user_id'] = auth()->id();
    
        // Upload image principale
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }
    
        // Upload images secondaires
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $file) {
                $images[] = $file->store('products', 'public');
            }
            $data['images'] = json_encode($images); 
        }
        
        
        $product = Product::create($data);
    
        return response()->json($product, 201);
    }

    // Afficher un produit
    public function show($id)
    {
        $product = Product::with('shop','category')->find($id);
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
