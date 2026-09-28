<?php

namespace App\Http\Middleware;

use App\Models\Hospital;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveHospital
{
    /**
     * Resolve RS berdasarkan parameter {hospital} (slug) pada route.
     * Menolak link yang tidak ada / tidak aktif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('hospital');

        if ($slug instanceof Hospital) {
            $hospital = $slug;
        } else {
            $hospital = Hospital::where('slug', $slug)->first();
        }

        if (! $hospital) {
            abort(404);
        }

        if (! $hospital->is_active) {
            return response()->view('hospital.inactive', [
                'hospital' => $hospital,
            ], 403);
        }

        $request->attributes->set('hospital', $hospital);
        view()->share('hospital', $hospital);

        return $next($request);
    }
}
