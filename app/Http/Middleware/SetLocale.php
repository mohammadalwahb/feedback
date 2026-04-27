<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    protected array $supported = ['en', 'ku', 'ar'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale');

        if (! $locale) {
            $locale = $request->getPreferredLanguage($this->supported);
        }

        if (! $locale || ! in_array($locale, $this->supported, true)) {
            $locale = config('app.locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
