@php
    use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
    use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

    $baseValidationOverview = ValidationOverview::make($this);

    $validationOverview = match (true) {
        method_exists($this, ($actionMethodName = $this->getMountedAction()->getName() . "ValidationOverview")) => $this->{$actionMethodName}($baseValidationOverview),
        method_exists($this, 'actionValidationOverview') => $this->actionValidationOverview($baseValidationOverview, $action),
        default => ValidationOverviewPlugin::get()->configureValidationOverview($baseValidationOverview, $this, $action)
    };
@endphp

@if ($validationOverview && $validationOverview->isVisible())
    {{ $validationOverview }}
@endif

