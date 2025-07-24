<?php

namespace wimbo\Onlydev\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Routing\Controller;

class OnlydevController extends Controller
{
    public function onlydevChangeUser(User $user)
    {
        $this->checkLocal();

        session()->flush();

        if (class_exists(\Laravel\Jetstream\JetstreamServiceProvider::class)) {
            // Jetstream est installé, allons-y comme des seigneurs
            Auth::login($user);
        } else {
            // Pas de Jetstream ? Aucun souci, on incante directement via l’ID
            Auth::loginUsingId($user->id);
        }

        session()->put([
            'password_hash_' . Auth::getDefaultDriver() => $user->getAuthPassword(),
        ]);

        request()->session()->regenerate();

        return redirect('/')->with('success', 'Utilisateur changé avec succès');
    }

    private function checkLocal()
    {
        $whitelist = ['127.0.0.1', '::1'];

        if (!in_array($_SERVER['REMOTE_ADDR'], $whitelist) || env('APP_ENV') !== 'local') {
            abort(403, 'Accès interdit');
        }
    }
}
