<?php

use App\Http\Middleware\AuthenticateHospital;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\ResolveHospital;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'hospital.auth' => AuthenticateHospital::class,
            'hospital.resolve' => ResolveHospital::class,
            'security.headers' => SecurityHeaders::class,
            'superadmin' => EnsureSuperAdmin::class,
        ]);

        $middleware->appendToGroup('web', SecurityHeaders::class);

        // SSO provider mengirim POST (hidden form) tanpa CSRF token Laravel.
        $middleware->validateCsrfTokens(except: [
            'sso/callback',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Tangani sesi/CSRF kedaluwarsa (419) dengan redirect ke halaman login
        // alih-alih menampilkan halaman "Page Expired".
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Silakan muat ulang halaman.',
                ], 419);
            }

            $message = 'Sesi Anda telah berakhir. Silakan masuk kembali.';

            // Halaman RS: kembalikan ke login RS yang sesuai.
            if ($request->is('rs/*')) {
                $hospital = $request->route('hospital');
                $slug = is_object($hospital) ? $hospital->slug : $hospital;

                if ($slug) {
                    return redirect()->route('hospital.login', ['hospital' => $slug])->with('error', $message);
                }
            }

            return redirect()->route('login')->with('error', $message);
        });
    })->create();
