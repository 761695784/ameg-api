<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/settings
     * Renvoie les paramètres publics du site (logo, whatsapp, emails, réseaux sociaux)
     * pour que le frontend Next.js les affiche dans le header/footer.
     */
    public function index()
    {
        $keys = [
            'logo', 'whatsapp_number', 'contact_email', 'contact_phone',
            'address', 'facebook_url', 'instagram_url', 'linkedin_url',
        ];

        $settings = collect($keys)->mapWithKeys(fn ($key) => [$key => Setting::get($key)]);

        return response()->json($settings);
    }

    /**
     * PUT /api/admin/settings (protégé)
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => ['required', 'array'],
        ]);

        foreach ($data['settings'] as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(['message' => 'Paramètres mis à jour']);
    }
}
