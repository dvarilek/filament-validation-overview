@php
    use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
    use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

    $validationOverviewPlugin = ValidationOverviewPlugin::get();

    $validationOverview = ValidationOverview::make()
        ->livewire($this);
@endphp

@if ($validationOverview = $validationOverviewPlugin->configureValidationOverview($validationOverview, $this, $this->getMountedAction()))
    {{ $validationOverview }}
@endif

