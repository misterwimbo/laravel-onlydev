<?php

namespace wimbo\Onlydev\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectOnlydevBar
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        if (
            app()->environment('local') &&
            config('app.debug') &&
            $response instanceof \Illuminate\Http\Response &&
            str_contains($response->headers->get('Content-Type'), 'text/html')
        ) {
            $content = $response->getContent();
            $toolbar = view('onlydev::index')->render();

            // Injecter juste avant </body>
            $content = str_replace('</body>', $toolbar . '</body>', $content);

            $response->setContent($content);
        }

        return $response;
    }
}
