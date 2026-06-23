<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuideMiddleware
{
    /**
     * Handle an incoming request.
     * Faculty with guide authority or assigned students can access the Guide Dashboard.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Allow guide faculty or any faculty with assigned students
        if ($user->isFaculty() && ($user->isGuideFaculty() || $user->students()->exists())) {
            return $next($request);
        }

        abort(403, 'Unauthorized access. Only Guide faculty can access the Guide Dashboard.');
    }
}
