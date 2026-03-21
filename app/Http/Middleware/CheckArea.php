<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckArea
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, ...$areas)
    {
        $user = $request->user();

        if (!in_array($user->area->codigo, $areas)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
