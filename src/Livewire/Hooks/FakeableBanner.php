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
                $indicator = static::indicatorHtml();

                // Inject inside the root element's closing tag to avoid creating a second root element.
                $lastClosingTag = strrpos($html, '</');

                if ($lastClosingTag === false) {
                    return $html.$indicator;
                }

                return substr($html, 0, $lastClosingTag).$indicator.substr($html, $lastClosingTag);
            };
        });
    }

    public static function indicatorHtml(): string
    {
        return '<div id="wirefake-indicator" style="position:fixed;top:0;left:0;right:0;background:#f59e0b;color:#000;padding:6px 12px;font-size:13px;font-family:sans-serif;z-index:9999;text-align:center;pointer-events:none;opacity:0.95;">WireFake is active — component data has been filled with fake values</div>';
    }

    public static function resetIndicator(): void
    {
        static::$indicatorInjected = false;
    }
}
