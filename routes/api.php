<?php

use App\Http\Controllers\CatalogImportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductPdfController;
use App\Http\Controllers\ProjectStudyRequestController;
use App\Http\Controllers\QuoteRequestController;
use App\Http\Controllers\RealisationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SubcategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques (front-office Next.js)
|--------------------------------------------------------------------------
*/

Route::get('/settings', [SettingController::class, 'index']);

Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{slug}', [CategoryController::class, 'show']);
Route::get('/categories/{categoryId}/subcategories', [SubcategoryController::class, 'index']);

Route::get('/brands', [BrandController::class, 'index']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/products/{slug}/technical-sheet', [ProductPdfController::class, 'download']);

Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{slug}', [ServiceController::class, 'show']);

Route::get('/realisations', [RealisationController::class, 'index']);
Route::get('/realisations/{slug}', [RealisationController::class, 'show']);

// Formulaires publics
Route::post('/quote-requests', [QuoteRequestController::class, 'store']);
Route::post('/project-study-requests', [ProjectStudyRequestController::class, 'store']);
Route::post('/contact-messages', [ContactMessageController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Authentification admin (Sanctum SPA / cookies)
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
});



/*
|--------------------------------------------------------------------------
| Routes admin (back-office) — protégées par Sanctum
|--------------------------------------------------------------------------
| Pense à ajouter le middleware auth:sanctum une fois Sanctum installé :
| php artisan install:api
*/

Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    Route::apiResource('subcategories', SubcategoryController::class)->except(['index']);
    Route::apiResource('brands', BrandController::class)->except(['index']);
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
    Route::apiResource('realisations', RealisationController::class)->except(['index', 'show']);

    Route::apiResource('quote-requests', QuoteRequestController::class)->except(['store']);
    Route::apiResource('project-study-requests', ProjectStudyRequestController::class)->except(['store']);
    Route::apiResource('contact-messages', ContactMessageController::class)->except(['store']);

    Route::put('/settings', [SettingController::class, 'update']);

    // Importation de catalogue (Excel + ZIP d'images)
    // // Import de catalogue : réservé aux utilisateurs connectés (auparavant accessible sans authentification)
Route::post('/catalog-import', [CatalogImportController::class, 'store'])->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Application mobile « Ameg Business » (jetons Sanctum)
|--------------------------------------------------------------------------
| L'app s'authentifie avec un jeton (Authorization: Bearer ...) et non par
| cookies. Les routes /api/admin/* acceptent aussi ce jeton.
*/

Route::post('/mobile/login', [MobileController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::post('/logout', [MobileController::class, 'logout']);
    Route::get('/me', [MobileController::class, 'me']);
    Route::get('/catalog', [MobileController::class, 'catalog']);
    Route::get('/quote-requests', [MobileController::class, 'quoteRequests']);
    Route::get('/quote-requests/{quoteRequest}', [MobileController::class, 'quoteRequest']);
    Route::patch('/quote-requests/{quoteRequest}', [MobileController::class, 'updateQuoteRequest']);
});
});
// Import de catalogue : réservé aux utilisateurs connectés (auparavant accessible sans authentification)
Route::post('/catalog-import', [CatalogImportController::class, 'store'])->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Application mobile « Ameg Business » (jetons Sanctum)
|--------------------------------------------------------------------------
| L'app s'authentifie avec un jeton (Authorization: Bearer ...) et non par
| cookies. Les routes /api/admin/* acceptent aussi ce jeton.
*/

Route::post('/mobile/login', [MobileController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::post('/logout', [MobileController::class, 'logout']);
    Route::get('/me', [MobileController::class, 'me']);
    Route::get('/catalog', [MobileController::class, 'catalog']);
    Route::get('/quote-requests', [MobileController::class, 'quoteRequests']);
    Route::get('/quote-requests/{quoteRequest}', [MobileController::class, 'quoteRequest']);
    Route::patch('/quote-requests/{quoteRequest}', [MobileController::class, 'updateQuoteRequest']);
});
