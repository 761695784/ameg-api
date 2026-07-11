<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ProductPdfController extends Controller
{
    /**
     * GET /api/products/{slug}/technical-sheet
     * Génère et télécharge la fiche technique PDF brandée AMEG pour un produit.
     *
     * @urlParam slug string required Le slug du produit. Example: ar612fx-armoire-inox-304-portes-coulissantes-doublees-toit-plat-3-etageres-reglables
     */
    public function download(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['brand', 'category', 'subcategory', 'characteristics', 'primaryImage'])
            ->firstOrFail();

        $pdf = Pdf::loadView('pdf.product-sheet', [
            'product' => $product,
            'groupedCharacteristics' => $product->characteristics->groupBy('groupe'),
            'imageDataUri' => $this->imageToDataUri($product->primaryImage?->path),
            'ameg' => [
                'phone' => config('ameg.phone'),
                'email' => config('ameg.email'),
                'website' => config('ameg.website'),
                'address' => config('ameg.address'),
            ],
        ])->setPaper('a4', 'portrait');

        $filename = 'Fiche-technique-' . $product->reference . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Convertit l'image stockée en data URI base64, pour que DomPDF l'affiche
     * de façon fiable sans dépendre d'une requête HTTP externe.
     */
    protected function imageToDataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $contents = Storage::disk('public')->get($path);
        $mime = Storage::disk('public')->mimeType($path) ?? 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }
}
