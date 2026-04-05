<?php

namespace TomEasterbrook\WireFake;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WireFakeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('wire-fake')
            ->hasConfigFile('fakeable')
            ->hasViews();
    }
}
