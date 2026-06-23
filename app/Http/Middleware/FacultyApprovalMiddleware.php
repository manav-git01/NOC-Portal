<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FacultyApprovalMiddleware
{
    /**
     * Handle an incoming request.
     * Only faculty with 'approval_faculty' permission can access approval routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Allow approval faculty (database-driven)
        if ($user->isFaculty() && $user->isApprovalFaculty()) {
            return $next($request);
        }

        if ($user->isFaculty()) {
            return redirect()->route('faculty.guide-dashboard')
                ->with('error', 'You do not have approval permissions. Redirected to Guide Dashboard.');
        }

        abort(403, 'Unauthorized access.');
    }
}
