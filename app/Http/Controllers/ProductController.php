<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Lister tous les produits
    public function index()
    {
        $products = Product::with('shop','category')->get();
        return response()->json($products);
    }

    // Créer un produit
    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required|string',
            'description'=>'nullable|string',
            'price'=>'required|numeric',
            'qte'=>'required|integer',
            'category_id'=>'required|exists:categories,id',
            'shop_id'=>'required|exists:shops,id',
            'image'=>'nullable|string',
            'images'=>'nullable|array',
            'pays_disponibilite'=>'nullable|array',
            'is_global'=>'nullable|boolean',
        ]);

        $product = Product::create($request->all());
        return response()->json($product,201);
    }

    // Afficher un produit
    public function show($id)
    {
        $product = Product::with('shop','category')->findOrFail($id);
        return response()->json($product);
    }

    // Mettre à jour un produit
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product);
    }

    // Supprimer un produit
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message'=>'Product deleted']);
    }
}
