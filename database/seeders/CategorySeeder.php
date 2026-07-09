<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Les 16 catégories officielles de l'arborescence du site AMEG International.
     */
    public function run(): void
    {
        $categories = [
            'Equipements de cuisson',
            'Equipements frigorifiques',
            'Préparation alimentaire',
            'Boulangerie',
            'Pâtisserie',
            'Bar',
            'Vaisselle & Art de la table',
            'Mobilier inox',
            'Laverie',
            'Hygiène & Entretien',
            'Equipements hôteliers',
            'Chambres froides',
            'Petit matériel',
            'Accessoires',
            'Consommables',
            'Blanchisserie',
        ];

        foreach ($categories as $order => $name) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'order' => $order, 'is_active' => true]
            );
        }
    }
}
