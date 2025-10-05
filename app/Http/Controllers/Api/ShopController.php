<?php

namespace App\Http\Controllers\Api;

use App\Models\Shop;
use App\Models\Commande;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\ShopCreatedMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ShopController extends Controller
{

    // Lister toutes les boutiques
    public function index()
    {
        $shops = Shop::with('user')->where('user_id', auth()->id())->latest()->get();
        return response()->json($shops);
    }


    // Créer une boutique
    public function store(Request $request)
    {
        // Messages personnalisés
        $messages = [
            'title.required' => 'Le nom de la boutique est obligatoire.',
            'title.string' => 'Le nom de la boutique doit être une chaîne de caractères.',
            'title.max' => 'Le nom de la boutique ne peut pas dépasser 255 caractères.',

            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',

            'address.required' => 'L’adresse est obligatoire.',
            'address.string' => 'L’adresse doit être une chaîne de caractères.',
            'address.max' => 'L’adresse ne peut pas dépasser 500 caractères.',

            'email.required' => 'L’email est obligatoire.',
            'email.email' => 'L’email doit être une adresse email valide.',
            'email.max' => 'L’email ne peut pas dépasser 255 caractères.',

            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.regex' => 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.',

            'category.required' => 'La catégorie est obligatoire.',
            'category.in' => 'La catégorie sélectionnée n’est pas valide.',

            'paymentOnDelivery.required' => 'Vous devez préciser si le paiement à la livraison est activé.',
            'salesTax.required' => 'Vous devez préciser si la TVA est activée.',

            'template.max' => 'Le nom du template ne peut pas dépasser 255 caractères.',
            'logo.image' => 'Le logo doit être une image.',
            'logo.max' => 'Le logo ne peut pas dépasser 2 Mo.',
        ];

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
        ], $messages);

        // Vérifier si la validation échoue
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Données invalides',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Déplace directement dans public/storage/logos
            $file->move(public_path('storage/logos'), $filename);
        
            $logo_url = url('storage/logos/' . $filename);
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
            'lien_shop' => $slug,
        ]);

        // Envoi du mail
        Mail::to(auth()->user()->email)->send(new ShopCreatedMail($shop));

        return response()->json($shop, 201);
    }


    public function showPublic($slug)
    {
        //consulter la boutique via son slug
        $shop = Shop::where('lien_shop', $slug)->with('products')->firstOrFail();

        if($shop->status !== 'complete'){
            return response()->json(['message' => 'Cette boutique n\'est pas activée pour le moment.'], 403);
        }

        return response()->json($shop);
    }



    // Afficher une boutique
    public function show($id)
    {
         // Récupérer la boutique ou renvoyer 404
         $shop = Shop::with('products')->findOrFail($id);

         // Statistiques dynamiques
         $stats = [
             [
                 'title' => 'Visiteurs ce mois',
                 'value' => $shop->visitors_count ?? 0,
                //  'change' => '+12%', 
                 'icon' => 'Eye',
                 'color' => 'text-primary'
             ],
             [
                 'title' => 'Commandes',
                 'value' => $shop->orders_count ?? 0,
                //  'change' => '+8%',
                 'icon' => 'ShoppingCart',
                 'color' => 'text-secondary'
             ],
             [
                 'title' => 'Chiffre d\'affaires',
                 'value' => $shop->revenue ?? 0,
                //  'change' => '+15%',
                 'icon' => 'TrendingUp',
                 'color' => 'text-accent'
             ],
             [
                 'title' => 'Produits actifs',
                 'value' => $shop->products()->count(),
                //  'change' => '+3%',
                 'icon' => 'Package',
                 'color' => 'text-success'
             ],
         ];
 
            // Commandes récentes
            $recentOrders = Commande::whereHas('orderable', function ($q) use ($id) {
                $q->where('shop_id', $id);
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($order) {
                // Si "customer" est une colonne JSON, on la décode
            $customer = is_string($order->customer) ? json_decode($order->customer, true) : $order->customer;
    
            return [
                'id'        => $order->id,
                'reference' => $order->reference,
                'product'   => [
                    'id'    => $order->orderable->id ?? null,
                    'title' => $order->orderable->title ?? null,
                ],
                'amount'    => $order->amount,
                'status'    => $order->status,
                'customer'  => [
                    'name'  => $customer['name'] ?? null,
                    'phone' => $customer['phone'] ?? null,
                    'city'  => $customer['city'] ?? null,
                ]
            ];
        });
    
    
         return response()->json([
             'shop' => $shop,
             'stats' => $stats,
             'recentOrders' => $recentOrders
         ]);
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
            return response()->json(['message' => 'Votre boutique ne peut pas être deésactivée'], 400);
        }

        $shop->status = 'desactived';
        $shop->update();

        return response()->json([
            'message' => 'La boutique a été désactivée',
            'shop' => $shop
        ]);
    }

    public function reactivate($id)
    {
        $shop = Shop::findOrFail($id);
    
        if ($shop->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }
    
        // Vérifier que le statut est 'desactived' ou 'incomplete'
        if (!in_array($shop->status, ['desactived', 'incomplete'])) {
            return response()->json(['message' => 'La boutique ne peut pas être réactivée'], 400);
        }
    
        $shop->status = 'complete';
        $shop->update();
    
        return response()->json([
            'message' => 'La boutique a été réactivée',
            'shop' => $shop
        ]);
    }
    

}

