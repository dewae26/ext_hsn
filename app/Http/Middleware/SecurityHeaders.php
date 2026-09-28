<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        $response->headers->set('X-XSS-Protection', '0');

        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    protected function contentSecurityPolicy(): string
    {
        $script = "'self' 'unsafe-inline'";
        $style = "'self' 'unsafe-inline'";
        $connect = "'self'";
        $font = "'self' data:";
        $img = "'self' data:";
        $worker = "'self' blob:";

        // Saat Vite dev server aktif (public/hot ada), izinkan origin-nya agar
        // CSS/JS/HMR di mode development tidak diblokir CSP.
        $devOrigin = $this->viteDevOrigin();

        if ($devOrigin) {
            $script .= ' '.$devOrigin;
            $style .= ' '.$devOrigin;
            $connect .= ' '.$devOrigin.' '.preg_replace('/^http/', 'ws', $devOrigin);
            $font .= ' '.$devOrigin;
            $img .= ' '.$devOrigin;
            $worker .= ' '.$devOrigin;
        }

        return implode('; ', [
            "default-src 'self'",
            "img-src {$img}",
            "style-src {$style}",
            "script-src {$script}",
            "font-src {$font}",
            "connect-src {$connect}",
            // 'self' (bukan 'none') agar viewer PDF bawaan browser dapat dirender di <iframe>.
            "object-src 'self'",
            "frame-src 'self'",
            "worker-src {$worker}",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
    }

    /**
     * Ambil origin Vite dev server dari file public/hot (mis. http://127.0.0.1:5173).
     */
    protected function viteDevOrigin(): ?string
    {
        if (! app()->environment('local')) {
            return null;
        }

        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return null;
        }

        $url = trim((string) file_get_contents($hotFile));
        $parts = parse_url($url);

        if (! $parts || empty($parts['host'])) {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'http';
        $host = $parts['host'];
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        return "{$scheme}://{$host}:{$port}";
    }
}
