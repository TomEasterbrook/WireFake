<?php

namespace TomEasterbrook\LivewireFakeable\Concerns;

use TomEasterbrook\LivewireFakeable\Services\FakeableGuard;
use TomEasterbrook\LivewireFakeable\Services\FakeableResolver;

trait HasFakeable
{
    public function fakeable(string $stateClass): void
    {
        if (! app(FakeableGuard::class)->allowed()) {
            return;
        }

        $resolver = new FakeableResolver;
        $resolved = $resolver->resolveStateClass($this, $stateClass);

        foreach ($resolved as $property => $value) {
            $this->{$property} = $value;
        }
    }
}
