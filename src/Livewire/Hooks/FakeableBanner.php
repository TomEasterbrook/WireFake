<?php

namespace TomEasterbrook\LivewireFakeable\Livewire\Hooks;

use Livewire\ComponentHook;
use TomEasterbrook\LivewireFakeable\Services\FakeableGuard;
use TomEasterbrook\LivewireFakeable\Services\FakeableResolver;

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

            $formResults = $resolver->resolveFormObjects($component);

            foreach ($formResults as $formProperty => $formValues) {
                foreach ($formValues as $property => $value) {
                    $component->{$formProperty}->{$property} = $value;
                }
            }
        });
    }
}
