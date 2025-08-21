@php
    use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
    use Filament\Pages\Page;

    $validationOverviewPlugin = ValidationOverviewPlugin::get();

    $validationOverviewPlugin
        ->forMountedAction(Page::class, fn ($action) => )
        ->for(Page::class, function ($validationOverview, Page $page) {

        })



@endphp

@if ($validationOverviewPlugin->isEnabledOnPage($this) && ($validationOverview = $validationOverviewPlugin->makeValidationOverview($this)))
    {{ $validationOverview }}
@endif