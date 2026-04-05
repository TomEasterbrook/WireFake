<?php

namespace TomEasterbrook\WireFake\Livewire\Hooks;

use Livewire\ComponentHook;
use TomEasterbrook\WireFake\Services\FakeableGuard;
use TomEasterbrook\WireFake\Services\FakeableResolver;

class FakeableBanner extends ComponentHook
{
    protected static bool $indicatorInjected = false;

    public function mount($params, $parent, $attributes): void
    {
        $guard = app(FakeableGuard::class);

        if (! $guard->allowed()) {
            return;
        }

        $resolver = new FakeableResolver;
        $resolved = $resolver->resolve($this->component);

        foreach ($resolved as $property => $value) {
            $this->component->{$property} = $value;
        }
    }

    public function render($view, $data): ?callable
    {
        return $this->indicatorFinisher();
    }

    public function renderIsland($name, $view, $data): ?callable
    {
        return $this->indicatorFinisher();
    }

    protected function indicatorFinisher(): ?callable
    {
        if (static::$indicatorInjected) {
            return null;
        }

        if (! config('fakeable.show_indicator')) {
            return null;
        }

        $guard = app(FakeableGuard::class);

        if (! $guard->allowed()) {
            return null;
        }

        static::$indicatorInjected = true;

        return function ($html, $replaceHtml) {
            $replaceHtml($html.static::indicatorHtml());
        };
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
