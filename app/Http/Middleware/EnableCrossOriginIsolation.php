<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnableCrossOriginIsolation
{
    /**
     * Set COOP/COEP headers untuk enable SharedArrayBuffer
     * (diperlukan untuk interactive Pyodide stdin di Web Worker).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set(
            'Cross-Origin-Embedder-Policy',
            'credentialless',
        );

        return $response;
    }
}
