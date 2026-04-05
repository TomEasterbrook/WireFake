<?php

namespace TomEasterbrook\LivewireFakeable\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \TomEasterbrook\LivewireFakeable\LivewireFakeable
 */
class LivewireFakeable extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \TomEasterbrook\LivewireFakeable\LivewireFakeable::class;
    }
}
