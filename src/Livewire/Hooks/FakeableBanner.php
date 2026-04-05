<?php

namespace TomEasterbrook\WireFake\Livewire\Hooks;

use Livewire\ComponentHook;
use TomEasterbrook\WireFake\Services\FakeableGuard;
use TomEasterbrook\WireFake\Services\FakeableResolver;

use function Livewire\after;

class FakeableBanner extends ComponentHook
{
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
    }
}
