<?php

namespace wimbo\Onlydev;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class OnlydevServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (env('APP_ENV') === 'local' && env('APP_DEBUG') === true) {

            $this->app['router']->pushMiddlewareToGroup('web', \wimbo\Onlydev\Http\Middleware\InjectOnlydevBar::class);
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

            // Charger les vues de ton package
            $this->loadViewsFrom(__DIR__ . '/resources/views', 'onlydev');

            // Partage de la variable __CURRENT_VIEW__ si ce n'est pas une vue système
            View::creator('*', function ($view) {
                $view->with('currentViewName', $view->getName());

                if (
                    !Str::contains($view->getPath(), 'layout') &&
                    !Str::contains($view->getPath(), 'auth') &&
                    !Str::contains($view->getPath(), 'sidebar') &&
                    !Str::contains($view->getPath(), 'partial') &&
                    !Str::contains($view->getPath(), 'components') &&
                    !Str::contains($view->getPath(), 'onlydev')
                ) {
                    View::share('__CURRENT_VIEW__', $view->getPath());
                }
            });
            
        }
    }

    public function register(): void
    {
        //
    }
}
