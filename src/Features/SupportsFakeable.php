<?php

namespace TomEasterbrook\WireFake\Features;

use Livewire\ComponentHook;
use TomEasterbrook\WireFake\FakeableGuard;
use TomEasterbrook\WireFake\FakeableResolver;

use function Livewire\after;

class SupportsFakeable extends ComponentHook
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
