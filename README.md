# Laravel OnlyDev

Un outil de développement pour Laravel réservé à l'environnement local.

## Installation

```bash
composer require wimbo/laravel-onlydev:dev-main
```

## Pré-requis

- Laravel 10, 11 ou 12
- PHP >= 8.1
- Jetstream (optionnel)
- Environnement `local` avec `APP_DEBUG=true`

## Fonctionnalités

- Affiche une DevBar flottante uniquement en local
- Liens vers : ((VS Code)
  - Vue actuelle 
  - Controller actuel
  - Route courante
  - Fichiers utiles (`.env`, `web.php`, `logs`)
- Sélecteur d'utilisateur (changement de session)
- Affichage de `request()->all()`

## Authentification

- Si Jetstream est installé, utilise `Auth::login($user)`
- Sinon, utilise `Auth::loginUsingId($user->id)`

## Sécurité

Fonctionne uniquement si :
- `APP_ENV` est `local`
- Adresse IP = `127.0.0.1` ou `::1`
