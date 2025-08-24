@php
    use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
    use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

    $plugin = ValidationOverviewPlugin::get();
    $baseValidationOverview = ValidationOverview::make($this);

    $validationOverview = method_exists($this, 'actionValidationOverview')
        ? $this->actionValidationOverview($baseValidationOverview, $this->getMountedAction())
        : ValidationOverviewPlugin::get()->configureValidationOverview($baseValidationOverview, $this, $this->getMountedAction());
@endphp

@if ($validationOverview && $validationOverview->isVisible())
    {{ $validationOverview }}
@endif

