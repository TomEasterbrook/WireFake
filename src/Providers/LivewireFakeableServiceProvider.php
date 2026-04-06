<?php

namespace TomEasterbrook\LivewireFakeable\Providers;

use Illuminate\Contracts\Http\Kernel;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use TomEasterbrook\LivewireFakeable\Http\Middleware\InjectFakeableBanner;
use TomEasterbrook\LivewireFakeable\Livewire\Hooks\FakeableBanner;

class LivewireFakeableServiceProvider extends PackageServiceProvider
{
    protected function getPackageBaseDir(): string
    {
        return dirname(__DIR__);
    }

    public function configurePackage(Package $package): void
    {
        $package
            ->name('livewire-fakeable')
            ->hasConfigFile('fakeable')
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Livewire::componentHook(FakeableBanner::class);

        if ($this->app->environment('local')) {
            $this->app->make(Kernel::class)->pushMiddleware(InjectFakeableBanner::class);
        }
    }
}
