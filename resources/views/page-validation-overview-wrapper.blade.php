@php
    use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
    use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

    $validationOverviewPlugin = ValidationOverviewPlugin::get();
    $validationOverview = ValidationOverview::make($this);
@endphp

@if ($validationOverview = $validationOverviewPlugin->configureValidationOverview($validationOverview, $this))
    {{ $validationOverview }}
@endif

