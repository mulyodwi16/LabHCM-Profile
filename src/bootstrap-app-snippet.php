<?php
// Add this inside bootstrap/app.php withMiddleware() block after breeze:install.
// This registers Spatie's role middleware alias so `role:admin` works in routes/web.php.
//
// use Illuminate\Foundation\Application;
// use Illuminate\Foundation\Configuration\Exceptions;
// use Illuminate\Foundation\Configuration\Middleware;
//
// return Application::configure(basePath: dirname(__DIR__))
//     ->withRouting(...)
//     ->withMiddleware(function (Middleware $middleware) {
//         $middleware->alias([
//             'role'              => \Spatie\Permission\Middleware\RoleMiddleware::class,
//             'permission'        => \Spatie\Permission\Middleware\PermissionMiddleware::class,
//             'role_or_permission'=> \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
//         ]);
//     })
//     ->withExceptions(function (Exceptions $exceptions) {})
//     ->create();
