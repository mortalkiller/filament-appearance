<?php

declare(strict_types=1);

namespace MortalKiller\FilamentAppearance\Http\Middleware;

use Closure;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Http\Request;
use MortalKiller\FilamentAppearance\Facades\Appearance;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gives Filament the colours of the palette the signed-in user chose. The panel registers its own
 * colours when it boots, before the user is known, so this runs with the authenticated routes and
 * registers on top of them. The matching CSS variables come from the head view.
 */
final class ApplyPalette
{
    public function handle(Request $request, Closure $next): Response
    {
        FilamentColor::register(Appearance::palette()->filamentColors());

        return $next($request);
    }
}
