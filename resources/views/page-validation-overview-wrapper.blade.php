@php
    use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
    use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

    $baseValidationOverview = ValidationOverview::make($this);

    $validationOverview = method_exists($this, 'validationOverview')
        ? $this->validationOverview($baseValidationOverview)
        : ValidationOverviewPlugin::get()->configureValidationOverview($baseValidationOverview, $this);
@endphp

@if ($validationOverview && $validationOverview->isVisible())
    {{ $validationOverview }}
@endif

