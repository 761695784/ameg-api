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
];
