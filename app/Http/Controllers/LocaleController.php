<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    protected array $supported = ['en', 'ku', 'ar'];

    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, $this->supported, true)) {
            $locale = 'en';
        }
        session(['locale' => $locale]);

        return redirect()->back();
    }
}
