<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\CatalogImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class CatalogImportController extends Controller
{
    /**
     * POST /api/admin/catalog-import
     *
     * Champs attendus (multipart/form-data) :
     *  - catalog_file : le fichier Excel (Produits / Caracteristiques / Images)
     *  - images_zip   : (optionnel) un ZIP contenant toutes les images référencées
     */
    public function store(Request $request, CatalogImportService $importService)
    {
        $request->validate([
            'catalog_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
            'images_zip' => ['nullable', 'file', 'mimes:zip'],
        ]);

        $importId = Str::uuid()->toString();
        $tempDir = storage_path("app/temp/import_{$importId}");
        mkdir($tempDir, 0755, true);

        // 1. Sauvegarde du fichier Excel
        $excelPath = $tempDir . '/' . $request->file('catalog_file')->getClientOriginalName();
        $request->file('catalog_file')->move($tempDir, basename($excelPath));

        // 2. Extraction du ZIP d'images (si fourni)
        $imagesDir = null;
        if ($request->hasFile('images_zip')) {
            $zipPath = $tempDir . '/images.zip';
            $request->file('images_zip')->move($tempDir, 'images.zip');

            $imagesDir = $tempDir . '/images_extracted';
            mkdir($imagesDir, 0755, true);

            $zip = new ZipArchive();
            if ($zip->open($zipPath) === true) {
                $zip->extractTo($imagesDir);
                $zip->close();

                // Si le ZIP contient un sous-dossier unique, on "aplatit" pour simplifier
                // la recherche de fichiers par nom (fichier_image de la feuille Images).
                $imagesDir = $this->flattenIfSingleSubdir($imagesDir);
            } else {
                $tempDir && $this->cleanup($tempDir);
                return response()->json(['message' => 'Impossible d\'ouvrir le fichier ZIP.'], 422);
            }
        }

        // 3. Lancement de l'import
        try {
            $report = $importService->import($excelPath, $imagesDir);
        } catch (\Throwable $e) {
            $this->cleanup($tempDir);
            return response()->json([
                'message' => 'Erreur lors de l\'import : ' . $e->getMessage(),
            ], 500);
        }

        // 4. Nettoyage des fichiers temporaires
        $this->cleanup($tempDir);

        return response()->json([
            'message' => 'Import terminé.',
            'report' => $report,
        ]);
    }

    protected function flattenIfSingleSubdir(string $dir): string
    {
        $items = array_values(array_diff(scandir($dir), ['.', '..']));

        if (count($items) === 1 && is_dir($dir . '/' . $items[0])) {
            return $dir . '/' . $items[0];
        }

        return $dir;
    }

    protected function cleanup(string $tempDir): void
    {
        if (is_dir($tempDir)) {
            $this->deleteDirectory($tempDir);
        }
    }

    protected function deleteDirectory(string $dir): void
    {
        $items = array_diff(scandir($dir), ['.', '..']);

        foreach ($items as $item) {
            $path = "{$dir}/{$item}";
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($dir);
    }
}
