<?php

namespace TomEasterbrook\WireFake\Livewire\Hooks;

use Livewire\ComponentHook;
use TomEasterbrook\WireFake\Services\FakeableGuard;
use TomEasterbrook\WireFake\Services\FakeableResolver;

use function Livewire\after;

class FakeableBanner extends ComponentHook
{
    protected static bool $indicatorInjected = false;

    public static function provide(): void
    {
        after('mount', function ($component) {
            $guard = app(FakeableGuard::class);

            if (! $guard->allowed()) {
                return;
            }

            $resolver = new FakeableResolver;
            $resolved = $resolver->resolve($component);

            foreach ($resolved as $property => $value) {
                $component->{$property} = $value;
            }
        });

        after('render', function ($component, $view, $data) {
            if (static::$indicatorInjected) {
                return;
            }

            if (! config('fakeable.show_indicator')) {
                return;
            }

            $guard = app(FakeableGuard::class);

            if (! $guard->allowed()) {
                return;
            }

            static::$indicatorInjected = true;

            return function ($html) {
                return $html.static::indicatorHtml();
            };
        });
    }

    public static function indicatorHtml(): string
    {
        return '<div id="wirefake-indicator" style="position:fixed;bottom:4px;right:4px;background:#f59e0b;color:#000;padding:2px 8px;border-radius:4px;font-size:12px;font-family:sans-serif;z-index:9999;pointer-events:none;opacity:0.9;">WireFake</div>';
    }

    public static function resetIndicator(): void
    {
        static::$indicatorInjected = false;
    }
}
