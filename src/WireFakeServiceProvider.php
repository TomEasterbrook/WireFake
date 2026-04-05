<?php

namespace TomEasterbrook\WireFake;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TomEasterbrook\WireFake\Features\SupportsFakeable;

class WireFakeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('wire-fake')
            ->hasConfigFile('fakeable')
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Livewire::componentHook(SupportsFakeable::class);
    }
}
