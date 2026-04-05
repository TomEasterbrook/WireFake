<?php

namespace TomEasterbrook\WireFake\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Fakeable
{
    public array $formatterArguments;

    public function __construct(
        public string $formatter,
        public ?int $seed = null,
        mixed ...$formatterArguments,
    ) {
        $this->formatterArguments = $formatterArguments;
    }
}
