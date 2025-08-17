<?php

declare(strict_types=1);

namespace Dvarilek\FilamentValidationOverview;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentValidationOverviewServiceProvider extends PackageServiceProvider
{

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-validation-overview')
            ->hasViews('filament-validation-overview')
            ->hasTranslations();
    }
}