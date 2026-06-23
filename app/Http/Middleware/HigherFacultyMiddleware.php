<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HigherFacultyMiddleware
{
    /**
     * Handle an incoming request.
     * Only faculty/higher_faculty with 'noc_authority' permission can access NOC routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Allow NOC authority (database-driven)
        if ($user->isHigherFaculty() && $user->isNocAuthority()) {
            return $next($request);
        }

        abort(403, 'Unauthorized access.');
    }
}
