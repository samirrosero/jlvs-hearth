<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestringirAccesoPublico
{
    protected array $rutasPermitidas = ['', 'planes'];

    protected array $prefijosAssets = ['build', 'css', 'js', 'img', 'storage', 'favicon.ico'];

    public function handle(Request $request, Closure $next)
    {
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        if (! $appHost || $appHost === '127.0.0.1' || $appHost === 'localhost') {
            return $next($request);
        }

        if ($request->getHost() !== $appHost) {
            return $next($request);
        }

        $path = $request->path();

        foreach ($this->prefijosAssets as $prefijo) {
            if ($path === $prefijo || str_starts_with($path, $prefijo . '/')) {
                return $next($request);
            }
        }

        if (in_array($path, $this->rutasPermitidas)) {
            return $next($request);
        }

        return redirect('/');
    }
}
