<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateHospital
{
    /**
     * Pastikan RS sudah login DAN sesi yang aktif memang milik RS pada link ini.
     * Mencegah RS A mengakses link RS B (isolasi antar RS).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hospital = $request->attributes->get('hospital');
        $guard = Auth::guard('hospital');

        if (! $guard->check()) {
            return redirect()->route('hospital.login', ['hospital' => $hospital->slug]);
        }

        if ($hospital && $guard->id() !== $hospital->id) {
            $guard->logout();
            $request->session()->invalidate();

            return redirect()->route('hospital.login', ['hospital' => $hospital->slug]);
        }

        return $next($request);
    }
}
