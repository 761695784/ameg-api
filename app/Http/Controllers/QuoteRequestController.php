<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuoteRequestRequest;
use App\Models\Product;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\DB;

class QuoteRequestController extends Controller
{
    /**
     * POST /api/quote-requests
     * Le visiteur envoie son panier de devis (plusieurs produits en une seule demande).
     */
    public function store(StoreQuoteRequestRequest $request)
    {
        $validated = $request->validated();

        $quoteRequest = DB::transaction(function () use ($validated) {
            $quoteRequest = QuoteRequest::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'company' => $validated['company'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'city' => $validated['city'] ?? null,
                'comment' => $validated['comment'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);

                $quoteRequest->items()->create([
                    'product_id' => $product->id,
                    'product_reference' => $product->reference,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'] ?? 1,
                ]);
            }

            return $quoteRequest;
        });

        // TODO: notifier l'admin par email (Mail::to(...)->send(new NewQuoteRequest($quoteRequest)))

        return response()->json($quoteRequest->load('items'), 201);
    }

    /**
     * GET /api/admin/quote-requests (protégé, back-office)
     */
    public function index()
    {
        return response()->json(
            QuoteRequest::with('items')->latest()->paginate(20)
        );
    }

    public function show(QuoteRequest $quoteRequest)
    {
        return response()->json($quoteRequest->load('items.product'));
    }

    /**
     * PATCH /api/admin/quote-requests/{quoteRequest}
     * Mettre à jour le statut / répondre à la demande.
     */
    public function update(\Illuminate\Http\Request $request, QuoteRequest $quoteRequest)
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:nouveau,en_cours,traite'],
            'admin_reply' => ['nullable', 'string'],
        ]);

        if (isset($data['admin_reply'])) {
            $data['replied_at'] = now();
        }

        $quoteRequest->update($data);

        return response()->json($quoteRequest);
    }

    public function destroy(QuoteRequest $quoteRequest)
    {
        $quoteRequest->delete();

        return response()->json(null, 204);
    }
}
