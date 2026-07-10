# Schéma de base de données — AMEG International

## Installation dans ton projet Laravel

1. Copie le contenu de `database/migrations/` dans `ton-projet/database/migrations/`
2. Copie le contenu de `app/Models/` dans `ton-projet/app/Models/`
3. Copie `database/seeders/CategorySeeder.php` dans `ton-projet/database/seeders/`
4. Ajoute dans `database/seeders/DatabaseSeeder.php` :
   ```php
   $this->call([
       CategorySeeder::class,
   ]);
   ```
5. Configure ta base de données dans `.env`
6. Lance :
   ```bash
   php artisan migrate
   php artisan db:seed --class=CategorySeeder
   ```

## Vue d'ensemble du schéma (18 tables)

### Catalogue
- **categories** — les 16 catégories officielles (avec SEO : meta_title, meta_description)
- **subcategories** — rattachées à une catégorie (SEO incluse)
- **brands** — marques des équipements
- **products** — référence, nom, prix FCFA, disponibilité, catégorie/sous-catégorie/marque, SEO, compteur de vues
- **product_images** — galerie (image principale + zoom, ordre d'affichage)
- **product_characteristics** — specs techniques groupées (groupe/caractéristique/valeur/unité), correspond exactement à la feuille `Caracteristiques` de ton Excel (reference, groupe, caracteristique, valeur, unite)
- **product_documents** — fiches techniques PDF, manuels, documentation (avec traçabilité du PDF fournisseur d'origine)

### Devis & contact
- **quote_requests** + **quote_request_items** — panier de devis multi-produits (une demande = plusieurs produits)
- **project_study_requests** + **project_study_documents** — formulaire "Étude de projet" avec pièces jointes
- **contact_messages** — messages du formulaire de contact

### Réalisations & services
- **services** — page "Nos Services"
- **realisations** + **realisation_images** + **realisation_service** (pivot) — projets réalisés par secteur (hôtels, restaurants, fast-foods, boulangeries, pâtisseries, collectivités), avec galerie et services associés

### Administration
- **users** — table Laravel par défaut (name, email, password), utilisée telle quelle pour le compte unique du propriétaire
- **settings** — table clé/valeur pour logo, WhatsApp, emails, réseaux sociaux (avec cache automatique via `Setting::get()` / `Setting::set()`)

## Points d'attention

- `products.availability` est un enum (`en_stock`, `sur_commande`, `rupture`) — à ajuster si tu veux d'autres statuts.
- `quote_request_items` duplique volontairement `product_reference` et `product_name` : si un produit est supprimé du catalogue plus tard, l'historique des devis reste lisible.
- Le champ `product_characteristics.groupe` correspond aux regroupements que tu as déjà utilisés (Tambour, Puissance, Général...) — pas de structure figée, entièrement piloté par les données importées.

## Contrôleurs (dans `app/Http/Controllers/Api/`)

| Contrôleur | Rôle |
|---|---|
| CategoryController | Liste catégories + CRUD admin |
| SubcategoryController | Liste sous-catégories par catégorie + CRUD admin |
| BrandController | Liste marques + CRUD admin |
| ProductController | Catalogue avec recherche (`q`), filtres (catégorie/sous-catégorie/marque/disponibilité), tri, pagination, fiche produit + produits similaires + CRUD admin |
| QuoteRequestController | Réception du panier de devis (multi-produits) + gestion admin (statut, réponse) |
| ProjectStudyRequestController | Formulaire étude de projet avec upload de documents + gestion admin |
| ContactMessageController | Formulaire de contact + gestion admin |
| ServiceController | Liste services + CRUD admin |
| RealisationController | Réalisations filtrables par secteur + CRUD admin (avec services liés) |
| SettingController | Paramètres publics (logo, whatsapp, contact...) + mise à jour admin |

## Form Requests (dans `app/Http/Requests/`)
Validation dédiée pour les 3 formulaires publics : `StoreQuoteRequestRequest`, `StoreProjectStudyRequestRequest`, `StoreContactMessageRequest`.

## Routes (`routes/api.php`)
Fichier prêt à coller tel quel (remplace le contenu existant, ou fusionne si tu as déjà des routes). Toutes les routes publiques sont en clair ; les routes admin sont sous `/api/admin/*` protégées par `auth:sanctum`.

⚠️ Pour activer Sanctum si ce n'est pas déjà fait :
```bash
php artisan install:api
```

## Commandes artisan combinées (modèle + migration + contrôleur en une fois)

```bash
php artisan make:model Category -mcr --api
php artisan make:model Subcategory -mcr --api
php artisan make:model Brand -mcr --api
php artisan make:model Product -mcr --api
php artisan make:model ProductImage -mcr --api
php artisan make:model ProductCharacteristic -mcr --api
php artisan make:model ProductDocument -mcr --api
php artisan make:model QuoteRequest -mcr --api
php artisan make:model QuoteRequestItem -mcr --api
php artisan make:model ProjectStudyRequest -mcr --api
php artisan make:model ProjectStudyDocument -mcr --api
php artisan make:model ContactMessage -mcr --api
php artisan make:model Setting -mcr --api
php artisan make:model Service -mcr --api
php artisan make:model Realisation -mcr --api
php artisan make:model RealisationImage -mcr --api
php artisan make:migration create_realisation_service_table
```

Pour chaque contrôleur généré (`-mcr --api` crée le contrôleur dans `app/Http/Controllers/`, pas dans `app/Http/Controllers/Api/`), déplace-le dans le sous-dossier `Api/` **ou** ajuste le namespace en haut du fichier collé pour qu'il corresponde à l'emplacement que tu choisis — l'important est que ça corresponde à ce que `routes/api.php` importe (`App\Http\Controllers\Api\...`).

## Service d'import du catalogue (Excel + ZIP images)

### Installation

```bash
composer require phpoffice/phpspreadsheet
php artisan storage:link
```

### Fichiers à copier
- `app/Services/CatalogImportService.php` — logique métier de l'import
- `app/Http/Controllers/Api/CatalogImportController.php` — endpoint d'upload
- La route est déjà ajoutée dans `routes/api.php` : `POST /api/admin/catalog-import` (protégée `auth:sanctum`)

Comme ce n'est ni un modèle ni une migration, il n'y a pas de commande `artisan` dédiée — crée juste les fichiers manuellement aux emplacements ci-dessus (ou `php artisan make:service` si tu as un package qui l'ajoute, sinon un simple fichier PHP suffit).

### Comment ça fonctionne

1. Tu envoies une requête `POST` multipart avec :
   - `catalog_file` : ton fichier `AMEG_Catalogue_Import.xlsx`
   - `images_zip` : (optionnel) un ZIP contenant toutes les images citées dans la feuille `Images` (`fichier_image`)
2. Le contrôleur extrait le ZIP dans un dossier temporaire, puis appelle `CatalogImportService::import()`
3. Le service traite les 3 feuilles **dans l'ordre** :
   - **Produits** → crée/actualise catégories, sous-catégories, marques, puis chaque produit (upsert par `reference`)
   - **Caracteristiques** → remplace proprement les caractéristiques de chaque produit concerné (pas de doublons si tu réimportes)
   - **Images** → copie chaque image dans `storage/app/public/products/{reference}/` et crée l'entrée `product_images` (la première image d'un produit devient automatiquement l'image principale)
4. Le tout est nettoyé (fichiers temporaires supprimés) et un rapport JSON est renvoyé :
   ```json
   {
     "message": "Import terminé.",
     "report": {
       "produits_crees": 210,
       "produits_mis_a_jour": 0,
       "categories_creees": 0,
       "sous_categories_creees": 12,
       "marques_creees": 8,
       "caracteristiques_importees": 521,
       "images_importees": 205,
       "images_manquantes": ["XYZ-123.png"],
       "erreurs": []
     }
   }
   ```

### Tester en local (curl)

```bash
curl -X POST http://localhost:8000/api/admin/catalog-import \
  -H "Authorization: Bearer TON_TOKEN_SANCTUM" \
  -F "catalog_file=@/chemin/vers/AMEG_Catalogue_Import.xlsx" \
  -F "images_zip=@/chemin/vers/images.zip"
```

### Points d'attention

- **Réimporter le même fichier est sans danger** : les produits sont mis à jour (upsert par référence), les caractéristiques sont remplacées proprement, les images ne sont pas dupliquées.
- Si une **catégorie de l'Excel ne correspond à aucune des 16 officielles**, le service la crée quand même automatiquement (pour ne jamais bloquer l'import) mais te le signale dans `erreurs` — à toi de vérifier ensuite dans l'admin.
- Si le **ZIP d'images n'est pas fourni** ou qu'un fichier image est manquant, l'import continue quand même (les produits sont créés sans photo) et la liste des fichiers manquants est renvoyée dans `images_manquantes`.
- Pense à augmenter `upload_max_filesize` et `post_max_size` dans ton `php.ini` si le ZIP d'images est volumineux (le catalogue avec 210 produits + photos peut facilement dépasser les limites par défaut de PHP).
## Prochaines étapes suggérées

1. Notifications email (devis, étude de projet, contact) — des `// TODO` sont déjà en place dans les contrôleurs
2. Authentification admin avec Sanctum (login/logout) si pas encore fait
3. Frontend Next.js qui consomme cette API
