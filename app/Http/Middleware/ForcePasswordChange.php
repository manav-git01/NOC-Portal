<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Routes that should be accessible even when password change is required.
     */
    protected array $exemptRoutes = [
        'password.change',
        'password.change.update',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * If the authenticated user has must_change_password = true,
     * redirect them to the password change form.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (
            auth()->check()
            && auth()->user()->must_change_password
            && !in_array($request->route()?->getName(), $this->exemptRoutes)
        ) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
