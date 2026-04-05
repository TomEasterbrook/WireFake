<?php

namespace TomEasterbrook\WireFake\Providers;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TomEasterbrook\WireFake\Livewire\Hooks\FakeableBanner;

class WireFakeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('wire-fake')
            ->hasConfigFile('fakeable')
            ->hasViews();
    }

    public function packageRegistered(): void
    {
        // Register before Livewire::boot() runs ComponentHookRegistry::boot(), so the hook
        // participates in the normal render / renderIsland pipelines (not only EventBus after()).
        $this->app->booting(function () {
            Livewire::componentHook(FakeableBanner::class);
        });
    }
}
