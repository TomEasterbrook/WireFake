<?php

namespace TomEasterbrook\LivewireFakeable\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Fakeable
{
    public array $formatterArguments;

    public function __construct(
        public string|array|null $formatter = null,
        public ?int $seed = null,
        public int $count = 1,
        array $formatterArguments = [],
        mixed ...$variadicArguments,
    ) {
        $this->formatterArguments = $formatterArguments !== [] ? $formatterArguments : $variadicArguments;
    }
}
