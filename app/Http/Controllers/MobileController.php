<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * API dédiée à l'application mobile « Ameg Business » (devis / factures).
 *
 * Authentification par jeton Sanctum (personal access token) :
 *   POST /api/mobile/login  -> { token, user }
 * puis en-tête « Authorization: Bearer <token> » sur les autres routes.
 */
class MobileController extends Controller
{
    /**
     * POST /api/mobile/login
     * Body : email, password, device_name (optionnel)
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        $token = $user->createToken($data['device_name'] ?? 'ameg-business-mobile', ['mobile']);

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => $user->only(['id', 'name', 'email']),
        ]);
    }

    /**
     * POST /api/mobile/logout — révoque le jeton utilisé.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Déconnecté.']);
    }

    /**
     * GET /api/mobile/me
     */
    public function me(Request $request)
    {
        return response()->json($request->user()->only(['id', 'name', 'email']));
    }

    /**
     * GET /api/mobile/catalog?updated_since=2026-09-01T00:00:00Z
     *
     * Catalogue complet (ou modifié depuis une date) en une seule réponse,
     * avec un résumé texte des caractéristiques techniques pour les devis.
     * Les produits désactivés sont renvoyés avec is_active=false pour que
     * l'app puisse les masquer.
     */
    public function catalog(Request $request)
    {
        $request->validate([
            'updated_since' => ['nullable', 'date'],
        ]);

        $serverTime = now()->toIso8601String();

        $products = Product::query()
            ->with([
                'brand:id,name',
                'category:id,name',
                'subcategory:id,name',
                'characteristics',
            ])
            ->when(
                $request->filled('updated_since'),
                fn ($q) => $q->where('updated_at', '>=', $request->date('updated_since'))
            )
            ->orderBy('id')
            ->get()
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'reference' => $p->reference,
                'name' => $p->name,
                'description' => $this->plainText($p->description, 600),
                'price_fcfa' => $p->price_fcfa !== null ? (int) round((float) $p->price_fcfa) : null,
                'availability' => $p->availability,
                'is_active' => (bool) $p->is_active,
                'brand' => $p->brand?->name,
                'category' => $p->category?->name,
                'subcategory' => $p->subcategory?->name,
                'characteristics' => $this->characteristicsSummary($p),
                'updated_at' => $p->updated_at?->toIso8601String(),
            ]);

        return response()->json([
            'server_time' => $serverTime,
            'count' => $products->count(),
            'products' => $products,
        ]);
    }

    /**
     * GET /api/mobile/quote-requests?status=nouveau|en_cours|traite
     * 50 dernières demandes avec leurs produits.
     */
    public function quoteRequests(Request $request)
    {
        $request->validate([
            'status' => ['nullable', 'in:nouveau,en_cours,traite'],
        ]);

        $items = QuoteRequest::with('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->limit(50)
            ->get();

        return response()->json(['data' => $items]);
    }

    /**
     * GET /api/mobile/quote-requests/{id}
     */
    public function quoteRequest(QuoteRequest $quoteRequest)
    {
        return response()->json($quoteRequest->load('items'));
    }

    /**
     * PATCH /api/mobile/quote-requests/{id}
     * Body : status (nouveau|en_cours|traite), admin_reply (optionnel)
     */
    public function updateQuoteRequest(Request $request, QuoteRequest $quoteRequest)
    {
        $data = $request->validate([
            'status' => ['nullable', 'in:nouveau,en_cours,traite'],
            'admin_reply' => ['nullable', 'string'],
        ]);

        if (isset($data['admin_reply'])) {
            $data['replied_at'] = now();
        }

        $quoteRequest->update($data);

        return response()->json($quoteRequest->load('items'));
    }

    // ------------------------------------------------------------------

    private function plainText(?string $html, int $limit): string
    {
        if (! $html) {
            return '';
        }
        $text = html_entity_decode(strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</li>'], "\n", $html)));
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n\s*\n+/", "\n", trim($text));

        return Str::limit($text, $limit);
    }

    /** « Capacité : 10 kg ; Puissance : 3 kW ; … » (limité pour tenir dans une ligne de devis) */
    private function characteristicsSummary(Product $product): string
    {
        return Str::limit(
            $product->characteristics
                ->map(fn ($c) => trim($c->caracteristique.' : '.$c->valeur.($c->unite ? ' '.$c->unite : '')))
                ->implode(' ; '),
            500
        );
    }
}
