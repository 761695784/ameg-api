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
- **users** — migration qui ajoute `role` (admin/super_admin) et `is_active` à la table Laravel par défaut
- **settings** — table clé/valeur pour logo, WhatsApp, emails, réseaux sociaux (avec cache automatique via `Setting::get()` / `Setting::set()`)

## Points d'attention

- `products.availability` est un enum (`en_stock`, `sur_commande`, `rupture`) — à ajuster si tu veux d'autres statuts.
- `quote_request_items` duplique volontairement `product_reference` et `product_name` : si un produit est supprimé du catalogue plus tard, l'historique des devis reste lisible.
- Le champ `product_characteristics.groupe` correspond aux regroupements que tu as déjà utilisés (Tambour, Puissance, Général...) — pas de structure figée, entièrement piloté par les données importées.
- Pense à ajouter `role` et `is_active` au `$fillable` et `$casts` de ton `App\Models\User` existant (je n'ai pas touché ce fichier pour ne pas écraser ta config Breeze/Jetstream/Sanctum).

## Prochaines étapes suggérées

1. Contrôleurs + routes API (produits, catalogue, filtres, recherche)
2. Service d'import Excel/CSV + ZIP images (reprend exactement la structure de `AMEG_Catalogue_Import.xlsx`)
3. Authentification admin (Sanctum recommandé pour une API consommée par Next.js)
4. Endpoints devis / étude de projet / contact
