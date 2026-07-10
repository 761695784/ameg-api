<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/login
     *
     * Le frontend Next.js doit d'abord appeler GET /sanctum/csrf-cookie
     * (avec credentials: 'include') avant cet appel, pour récupérer le
     * cookie XSRF-TOKEN nécessaire à la protection CSRF.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials, remember: true)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants incorrects.'],
            ]);
        }

        // Régénère l'ID de session pour éviter la fixation de session
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Connexion réussie.',
            'user' => Auth::user(),
        ]);
    }

    /**
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Déconnexion réussie.']);
    }

    /**
     * GET /api/user
     * Utilisé par le frontend pour savoir si l'admin est connecté
     * (au chargement de l'app, avant d'afficher le tableau de bord).
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
