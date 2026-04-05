<?php

namespace TomEasterbrook\WireFake\Concerns;

use TomEasterbrook\WireFake\Services\FakeableResolver;

trait HasFakeable
{
    public function fakeable(string $stateClass): void
    {
        $resolver = new FakeableResolver;
        $resolved = $resolver->resolveStateClass($this, $stateClass);

        foreach ($resolved as $property => $value) {
            $this->{$property} = $value;
        }
    }
}
