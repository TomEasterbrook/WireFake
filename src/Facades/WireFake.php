<?php

namespace TomEasterbrook\WireFake\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TomEasterbrook\WireFake\WireFake
 */
class WireFake extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TomEasterbrook\WireFake\WireFake::class;
    }
}
