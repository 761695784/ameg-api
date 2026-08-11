<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Destinataire des notifications internes AMEG International
    |--------------------------------------------------------------------------
    | Toutes les demandes (devis, étude de projet, contact) sont envoyées
    | à cette adresse. Modifie-la simplement dans ton fichier .env.
    */
    'admin_email' => env('AMEG_ADMIN_EMAIL', 'contactameginternational@gmail.com'),
    'admin_name' => env('AMEG_ADMIN_NAME', 'AMEG International'),

    /*
    |--------------------------------------------------------------------------
    | Coordonnées AMEG International
    |--------------------------------------------------------------------------
    | Utilisées dans la fiche technique PDF, le footer du site, le bouton
    | WhatsApp flottant, etc.
    */
    'phone' => env('AMEG_PHONE', '33 824 77 63 / +221 77 646 43 41'),
    'whatsapp_number' => env('AMEG_WHATSAPP', '221776464341'), // format international sans "+", pour les liens wa.me
    'email' => env('AMEG_CONTACT_EMAIL', 'contactameginternational@gmail.com'),
    'website' => env('AMEG_WEBSITE', 'ameginternational.com'),
    'address' => env('AMEG_ADDRESS', 'Dakar, POINT E Rue P-170 , Sénégal'),
];
