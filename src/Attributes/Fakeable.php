<?php

namespace TomEasterbrook\WireFake\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Fakeable
{
    public array $formatterArguments;

    public function __construct(
        public string|array|null $formatter = null,
        public ?int $seed = null,
        public int $count = 1,
        mixed ...$formatterArguments,
    ) {
        $this->formatterArguments = $formatterArguments;
    }
}
