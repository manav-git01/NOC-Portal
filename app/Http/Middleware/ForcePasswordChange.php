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
        if (auth()->check()) {
            $user = auth()->user();

            // 1. Force password change check
            if ($user->must_change_password && !in_array($request->route()?->getName(), $this->exemptRoutes)) {
                return redirect()->route('password.change');
            }

            // 2. Force profile details completion for students
            if ($user->isStudent()) {
                $isIncomplete = empty($user->phone)
                    || $user->phone === 'N/A'
                    || empty($user->department)
                    || empty($user->semester);

                if ($isIncomplete) {
                    $exempt = array_merge($this->exemptRoutes, [
                        'profile.settings',
                        'profile.settings.update',
                    ]);

                    if (!in_array($request->route()?->getName(), $exempt)) {
                        return redirect()->route('profile.settings')
                            ->with('warning', 'Please complete your profile details (department, semester, and phone number) to access your dashboard.');
                    }
                }
            }
        }

        return $next($request);
    }
}
