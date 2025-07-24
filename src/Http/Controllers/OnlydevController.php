<?php

namespace wimbo\Onlydev\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\Console\Output\BufferedOutput;

class OnlydevController extends Controller
{
    private $allowedCommands = [
        // Cache commands
        'cache:clear' => 'Vider le cache',
        'config:clear' => 'Vider config cache',
        'route:clear' => 'Vider routes cache',
        'view:clear' => 'Vider vues cache',
        'optimize:clear' => 'Vider tous les caches',
        
        // Database commands
        'migrate' => 'Lancer les migrations',
        'migrate:fresh --seed' => 'Reset DB + seeders',
        'db:seed' => 'Lancer les seeders',
        'migrate:rollback' => 'Rollback dernière migration',
        'migrate:status' => 'Statut des migrations',
    ];

    public function executeArtisanCommand(Request $request)
    {
        $this->checkLocal();

        $command = $request->input('command');

        if (!array_key_exists($command, $this->allowedCommands)) {
            return response()->json([
                'success' => false,
                'error' => 'Commande non autorisée'
            ], 403);
        }

        try {
            $output = new BufferedOutput();
            
            // Log de la commande exécutée
            Log::info('OnlyDev: Commande Artisan exécutée', [
                'command' => $command,
                'user_id' => auth()->id() ?? 'guest',
                'ip' => request()->ip()
            ]);

            // Gestion spéciale pour migrate:fresh --seed
            if ($command === 'migrate:fresh --seed') {
                Artisan::call('migrate:fresh', ['--seed' => true], $output);
            } else {
                // Parse la commande et ses arguments
                $parts = explode(' ', $command);
                $commandName = array_shift($parts);
                $arguments = [];
                
                foreach ($parts as $part) {
                    if (strpos($part, '--') === 0) {
                        $arguments[str_replace('--', '', $part)] = true;
                    }
                }
                
                Artisan::call($commandName, $arguments, $output);
            }

            $result = $output->fetch();

            return response()->json([
                'success' => true,
                'command' => $command,
                'output' => $result ?: 'Commande exécutée avec succès',
                'description' => $this->allowedCommands[$command]
            ]);

        } catch (\Exception $e) {
            Log::error('OnlyDev: Erreur lors de l\'exécution de la commande', [
                'command' => $command,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de l\'exécution: ' . $e->getMessage()
            ], 500);
        }
    }
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
