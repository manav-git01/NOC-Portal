<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'logout',
            'admin/students/import',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'faculty_approval' => \App\Http\Middleware\FacultyApprovalMiddleware::class,
            'higher_faculty_approval' => \App\Http\Middleware\HigherFacultyMiddleware::class,
            'guide_only' => \App\Http\Middleware\GuideMiddleware::class,
            'force_password_change' => \App\Http\Middleware\ForcePasswordChange::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\ForcePasswordChange::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
