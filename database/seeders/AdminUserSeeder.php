<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Crée (ou met à jour) le compte unique du propriétaire pour se connecter
     * au tableau de bord admin.
     *
     * ⚠️ Change l'email et le mot de passe ci-dessous avant de lancer le seeder,
     * puis change le mot de passe une fois connecté si tu veux plus de sécurité.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'contactameginternational@gmail.com'],
            [
                'name' => 'AMEG International',
                'password' => Hash::make('AMEG@2026'), // Change le mot de passe avant de lancer le seeder
            ]
        );
    }
}
