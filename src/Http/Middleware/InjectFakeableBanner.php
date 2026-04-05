<?php

namespace TomEasterbrook\LivewireFakeable\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use TomEasterbrook\LivewireFakeable\Services\FakeableGuard;

class InjectFakeableBanner
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
        <div id="fakeable-banner" style="position:fixed;bottom:0;left:0;right:0;z-index:99999;font-family:system-ui,-apple-system,sans-serif;font-size:12px;">
            <div id="fakeable-banner-expanded" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:4px 12px;background:linear-gradient(135deg,#1e1e2e,#2d2d44);border-top:2px solid #f59e0b;color:#e0e0e0;">
                <span style="display:inline-flex;align-items:center;gap:6px;">
                    <span style="background:#f59e0b;color:#1e1e2e;font-weight:700;padding:1px 6px;border-radius:3px;font-size:11px;letter-spacing:0.5px;">FAKEABLE</span>
                    <span>Components using <code style="background:#383850;padding:1px 4px;border-radius:3px;font-size:11px;">#[Fakeable]</code> attributes have their properties filled with fake data</span>
                </span>
                <button onclick="localStorage.setItem('fakeable-collapsed','1');document.getElementById('fakeable-banner-expanded').style.display='none';document.getElementById('fakeable-banner-collapsed').style.display='flex';" style="background:none;border:none;color:#e0e0e0;cursor:pointer;padding:0 4px;font-size:16px;line-height:1;opacity:0.7;" title="Collapse">&#x2715;</button>
            </div>
            <div id="fakeable-banner-collapsed" style="display:none;justify-content:flex-end;padding:0;">
                <button onclick="localStorage.removeItem('fakeable-collapsed');document.getElementById('fakeable-banner-collapsed').style.display='none';document.getElementById('fakeable-banner-expanded').style.display='flex';" style="background:#1e1e2e;border:1px solid #f59e0b;border-bottom:none;border-right:none;color:#f59e0b;cursor:pointer;padding:2px 8px;border-radius:4px 0 0 0;font-weight:700;font-size:11px;font-family:inherit;letter-spacing:0.5px;" title="Expand">F</button>
            </div>
        </div>
        <script>if(localStorage.getItem('fakeable-collapsed')){document.getElementById('fakeable-banner-expanded').style.display='none';document.getElementById('fakeable-banner-collapsed').style.display='flex';}</script>
        HTML;
    }
}
