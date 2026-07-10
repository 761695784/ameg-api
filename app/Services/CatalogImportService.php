<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCharacteristic;
use App\Models\ProductImage;
use App\Models\Subcategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CatalogImportService
{
    /**
     * Compteurs + erreurs remontés à l'admin après import.
     */
    protected array $report = [
        'produits_crees' => 0,
        'produits_mis_a_jour' => 0,
        'categories_creees' => 0,
        'sous_categories_creees' => 0,
        'marques_creees' => 0,
        'caracteristiques_importees' => 0,
        'images_importees' => 0,
        'images_manquantes' => [],
        'erreurs' => [],
    ];

    /**
     * @param  string  $excelPath        Chemin absolu vers le fichier Excel (3 feuilles : Produits, Caracteristiques, Images)
     * @param  string|null  $imagesDir   Chemin absolu vers le dossier contenant les images extraites du ZIP (peut être null si pas d'images fournies)
     */
    public function import(string $excelPath, ?string $imagesDir = null): array
    {
        $spreadsheet = IOFactory::load($excelPath);

        // L'ordre est important : Produits d'abord (crée les produits),
        // puis Caracteristiques et Images qui référencent les produits par leur "reference".
        $this->importProduits($spreadsheet->getSheetByName('Produits'));
        $this->importCaracteristiques($spreadsheet->getSheetByName('Caracteristiques'));
        $this->importImages($spreadsheet->getSheetByName('Images'), $imagesDir);

        return $this->report;
    }

    /* -----------------------------------------------------------------
     |  Feuille "Produits"
     |  Colonnes : reference, nom, marque, categorie, sous_categorie,
     |             description, prix_fcfa, disponibilite, image_fichier
     |----------------------------------------------------------------- */
    protected function importProduits(?\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        if (! $sheet) {
            $this->report['erreurs'][] = 'Feuille "Produits" introuvable dans le fichier Excel.';
            return;
        }

        $rows = $sheet->toArray(null, true, true, true);
        $header = array_shift($rows); // première ligne = en-têtes

        foreach ($rows as $index => $row) {
            $data = $this->mapRowToHeader($header, $row);

            $reference = trim((string) ($data['reference'] ?? ''));
            if ($reference === '') {
                continue; // ligne vide, on saute
            }

            try {
                DB::transaction(function () use ($data, $reference) {
                    $category = $this->findOrCreateCategory($data['categorie'] ?? null);
                    $subcategory = $this->findOrCreateSubcategory($category, $data['sous_categorie'] ?? null);
                    $brand = $this->findOrCreateBrand($data['marque'] ?? null);

                    $product = Product::updateOrCreate(
                        ['reference' => $reference],
                        [
                            'name' => trim((string) ($data['nom'] ?? $reference)),
                            'brand_id' => $brand?->id,
                            'category_id' => $category?->id,
                            'subcategory_id' => $subcategory?->id,
                            'description' => $data['description'] ?? null,
                            'price_fcfa' => $this->parsePrice($data['prix_fcfa'] ?? null),
                            'availability' => $this->normalizeAvailability($data['disponibilite'] ?? null),
                            'slug' => Str::slug($reference . '-' . ($data['nom'] ?? $reference)),
                        ]
                    );

                    $product->wasRecentlyCreated
                        ? $this->report['produits_crees']++
                        : $this->report['produits_mis_a_jour']++;
                });
            } catch (\Throwable $e) {
                $this->report['erreurs'][] = "Produit ligne {$index} ({$reference}) : {$e->getMessage()}";
                Log::error("CatalogImport - erreur produit {$reference}", ['exception' => $e]);
            }
        }
    }

    /* -----------------------------------------------------------------
     |  Feuille "Caracteristiques"
     |  Colonnes : reference, groupe, caracteristique, valeur, unite
     |----------------------------------------------------------------- */
    protected function importCaracteristiques(?\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        if (! $sheet) {
            return; // feuille optionnelle
        }

        $rows = $sheet->toArray(null, true, true, true);
        $header = array_shift($rows);

        // On regroupe par référence pour ne faire qu'un remplacement propre par produit
        $byReference = [];
        foreach ($rows as $row) {
            $data = $this->mapRowToHeader($header, $row);
            $reference = trim((string) ($data['reference'] ?? ''));
            if ($reference === '') {
                continue;
            }
            $byReference[$reference][] = $data;
        }

        foreach ($byReference as $reference => $caracteristiques) {
            $product = Product::where('reference', $reference)->first();

            if (! $product) {
                $this->report['erreurs'][] = "Caractéristiques ignorées : produit {$reference} introuvable (importe d'abord la feuille Produits).";
                continue;
            }

            // On repart d'une base propre à chaque import pour éviter les doublons
            $product->characteristics()->delete();

            foreach ($caracteristiques as $order => $carac) {
                ProductCharacteristic::create([
                    'product_id' => $product->id,
                    'groupe' => trim((string) ($carac['groupe'] ?? 'Général')),
                    'caracteristique' => trim((string) ($carac['caracteristique'] ?? '')),
                    'valeur' => trim((string) ($carac['valeur'] ?? '')),
                    'unite' => $carac['unite'] ?? null,
                    'order' => $order,
                ]);
                $this->report['caracteristiques_importees']++;
            }
        }
    }

    /* -----------------------------------------------------------------
     |  Feuille "Images"
     |  Colonnes : reference, fichier_image, type, source_pdf
     |----------------------------------------------------------------- */
    protected function importImages(?\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, ?string $imagesDir): void
    {
        if (! $sheet) {
            return;
        }

        $rows = $sheet->toArray(null, true, true, true);
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $data = $this->mapRowToHeader($header, $row);
            $reference = trim((string) ($data['reference'] ?? ''));
            $fichier = trim((string) ($data['fichier_image'] ?? ''));

            if ($reference === '' || $fichier === '') {
                continue;
            }

            $product = Product::where('reference', $reference)->first();
            if (! $product) {
                $this->report['erreurs'][] = "Image ignorée : produit {$reference} introuvable.";
                continue;
            }

            if (! $imagesDir) {
                $this->report['images_manquantes'][] = $fichier;
                continue;
            }

            $sourcePath = rtrim($imagesDir, '/') . '/' . $fichier;

            if (! file_exists($sourcePath)) {
                $this->report['images_manquantes'][] = $fichier;
                continue;
            }

            // Destination : storage/app/public/products/{reference}/{fichier}
            $destRelative = "products/{$reference}/{$fichier}";
            Storage::disk('public')->put($destRelative, file_get_contents($sourcePath));

            $alreadyHasPrimary = $product->images()->where('is_primary', true)->exists();

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'path' => $destRelative],
                [
                    'alt_text' => $product->name,
                    'is_primary' => ! $alreadyHasPrimary,
                    'order' => $product->images()->count(),
                ]
            );

            $this->report['images_importees']++;
        }
    }

    /* -----------------------------------------------------------------
     |  Helpers
     |----------------------------------------------------------------- */

    protected function mapRowToHeader(array $header, array $row): array
    {
        $data = [];
        foreach ($header as $col => $key) {
            if (! $key) {
                continue;
            }
            $data[trim((string) $key)] = $row[$col] ?? null;
        }
        return $data;
    }

    protected function findOrCreateCategory(?string $name): ?Category
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $slug = Str::slug($name);
        $category = Category::where('slug', $slug)->first();

        if (! $category) {
            $category = Category::create([
                'name' => $name,
                'slug' => $slug,
                'order' => Category::max('order') + 1,
            ]);
            $this->report['categories_creees']++;
            $this->report['erreurs'][] = "Nouvelle catégorie créée automatiquement : \"{$name}\" (vérifie qu'elle correspond bien aux 16 catégories officielles).";
        }

        return $category;
    }

    protected function findOrCreateSubcategory(?Category $category, ?string $name): ?Subcategory
    {
        $name = trim((string) $name);
        if ($category === null || $name === '') {
            return null;
        }

        $slug = Str::slug($name);
        $subcategory = Subcategory::where('category_id', $category->id)->where('slug', $slug)->first();

        if (! $subcategory) {
            $subcategory = Subcategory::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => $slug,
                'order' => Subcategory::where('category_id', $category->id)->max('order') + 1,
            ]);
            $this->report['sous_categories_creees']++;
        }

        return $subcategory;
    }

    protected function findOrCreateBrand(?string $name): ?Brand
    {
        $name = trim((string) $name);
        if ($name === '') {
            return null;
        }

        $brand = Brand::where('name', $name)->first();

        if (! $brand) {
            $brand = Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
            $this->report['marques_creees']++;
        }

        return $brand;
    }

    protected function parsePrice($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Nettoie "1 250 000 FCFA" -> 1250000
        $clean = preg_replace('/[^0-9.,]/', '', (string) $value);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }

    protected function normalizeAvailability($value): string
    {
        $value = Str::lower(trim((string) $value));

        return match (true) {
            str_contains($value, 'stock') => 'en_stock',
            str_contains($value, 'rupture') => 'rupture',
            default => 'sur_commande',
        };
    }
}
