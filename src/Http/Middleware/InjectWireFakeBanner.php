<?php

namespace TomEasterbrook\WireFake\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use TomEasterbrook\WireFake\Services\FakeableGuard;

class InjectWireFakeBanner
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('fakeable.show_indicator')) {
            return $response;
        }

        if (! app(FakeableGuard::class)->allowed()) {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false) {
            return $response;
        }

        $pos = strripos($content, '</body>');

        if ($pos !== false) {
            $content = substr($content, 0, $pos).static::bannerHtml().substr($content, $pos);
            $response->setContent($content);
        }

        return $response;
    }

    public static function bannerHtml(): string
    {
        return <<<'HTML'
        <div id="wirefake-banner" style="position:fixed;bottom:0;left:0;right:0;z-index:99999;display:flex;align-items:center;justify-content:center;gap:8px;padding:4px 12px;background:linear-gradient(135deg,#1e1e2e,#2d2d44);border-top:2px solid #f59e0b;font-family:system-ui,-apple-system,sans-serif;font-size:12px;color:#e0e0e0;">
            <span style="display:inline-flex;align-items:center;gap:6px;">
                <span style="background:#f59e0b;color:#1e1e2e;font-weight:700;padding:1px 6px;border-radius:3px;font-size:11px;letter-spacing:0.5px;">WIREFAKE</span>
                <span>Component data has been filled with fake values</span>
            </span>
        </div>
        HTML;
    }
}
