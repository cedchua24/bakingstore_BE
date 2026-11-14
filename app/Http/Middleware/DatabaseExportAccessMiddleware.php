<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DatabaseExportAccessMiddleware
{
    /**
     * Allow database exports for authenticated roles 1, 2, and 3.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! in_array((int) $request->user()->role_as, [1, 2, 3], true)) {
            return response()->json([
                'message' => 'Your account is not authorized to export the database.',
            ], 403);
        }

        return $next($request);
    }
}
