<?php

namespace App\Providers;

use App\Models\Hospital;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureRateLimiters();
    }

    protected function configureRateLimiters(): void
    {
        RateLimiter::for('otp-request', function (Request $request) {
            $phone = Hospital::normalizePhone($request->input('phone')) ?: $request->ip();

            return Limit::perMinutes(15, (int) config('hasnurverif.rate_limit.otp_request_per_15_minutes', 3))
                ->by('otp-request:'.$phone.'|'.$request->ip());
        });

        RateLimiter::for('otp-verify', function (Request $request) {
            $phone = $request->session()->get('hospital_pending_phone') ?: $request->ip();

            return Limit::perMinutes(15, (int) config('hasnurverif.rate_limit.otp_verify_per_15_minutes', 10))
                ->by('otp-verify:'.$phone.'|'.$request->ip());
        });

        RateLimiter::for('lookup', function (Request $request) {
            $hospital = $request->attributes->get('hospital');
            $hospitalId = $hospital?->id ?? 0;

            return Limit::perMinute((int) config('hasnurverif.rate_limit.lookup_per_minute', 30))
                ->by('lookup:'.$hospitalId.'|'.$request->ip());
        });
    }
}
