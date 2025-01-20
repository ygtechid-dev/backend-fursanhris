<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlatformAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $platform): Response
    {
        // dd($platform);
        if (!$request->user()->tokenCan("$platform-access")) {
            return response()->json([
                'message' => "Unauthorized for $platform access"
            ], 403);
        }
        return $next($request);
    }
}
