# Filament Validation Overview

This plugin adds a validation overview for complex Filament forms that contain many fields.

## Getting Started 

### Instalation
```bash
composer require dvarilek/filament-validation-overview:^1.0
```

## Configuration

Validation overview can either be configured on [every Livewire page](#pege-configuration) component manually, or by registering a configuration through 
the [admin panel provider](#panel-configuration). Doing the latter is the reccomended approach, mainly because it ultimately makes things more managable.

### Pege Configuration

Declare `validationOverview()` method to configure the validation overview.

```php
use Filament\Resources\Pages\EditRecord;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

class EditCustomer extends EditRecord
{
    // ...

    public function validationOverview(ValidationOverview $validationOverview): ValidationOverview
    {
        return $validationOverview
            ->description("Custom description");
    }
}
```

To configure validation overview for a specific action, prefix the method name with the given action.
For example, if your action is named `editCustomer`, it should be `editCustomerValidationOverview()`.

```php
use Filament\Resources\Pages\EditRecord;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

class EditCustomer extends EditRecord
{
    // ...

    public function editCustomerValidationOverview(ValidationOverview $validationOverview): ValidationOverview
    {
        return $validationOverview
            ->description("Custom description for edit customer action");
    }
}
```

Additionally, declaring `actionValidationOverview()` allows you to configure all action validation overviews on that Livewire component.
The former example of specifying the action name in the method signature takes priority if present.

```php
use Filament\Resources\Pages\EditRecord;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;
use Filament\Actions\Action;

class EditCustomer extends EditRecord
{
    // ...

    public function actionValidationOverview(ValidationOverview $validationOverview, Action $action): ValidationOverview
    {
        return $validationOverview
            ->description("Custom description for " . $action->getName());
    }
}
```

***

### Panel Configuration

To configure validation overview centrally, register the `Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin` plugin 
in your admin panel provider. this is the recommended approach.
```php
use Filament\Panel;
use Filament\PanelProvider;
use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ...
            ->plugins([
                ValidationOverviewPlugin::make()
            ]);
    }
}
```

The `default()` method allows you to set a default validation overview configuration (excluding validation overview in action modals).

// TODO: This should also set default for action modals, actionDefault takes priority.

```php
use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
use Filament\Schemas\Contracts\HasSchemas;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

ValidationOverviewPlugin::make()
    ->default(function (ValidationOverview $validationOverview) {
        $validationOverview->description('Default description for validation overviews')
    })
```



For configuring the validation overview instance itself, see [Validation overview configuration](#validation-overview-configuration)

The `actionDefault()`method allows you to set a default validation overview configuration.

```php
use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
use Filament\Schemas\Contracts\HasSchemas;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

ValidationOverviewPlugin::make()
    ->actionDefault(function (ValidationOverview $validationOverview) {
        $validationOverview->description('Default description for validation overviews')
    })
```

The `forResource()` method allows configuration for specific Filament Resources. 
```php
use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
use Filament\Schemas\Contracts\HasSchemas;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

ValidationOverviewPlugin::make()
    ->forResource(CustomerResource::class, function (ValidationOverview $validationOverview, HasSchemas $livewire) {
        // The description will get changed on the suitable pages belongting to this Resource (most likely CreateCustomer and EditCustomer)
        $validationOverview->description("Please resolve the following errors with this customer");
    })
```

The `for()` method allows configuration of validation overviews for specific Livewire page components and takes priority over resource configurations.
This doesn't change validation for specific action modals on the page(s).

```php
use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
use Filament\Schemas\Contracts\HasSchemas;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

ValidationOverviewPlugin::make()
    ->for(EditCustomers::class, function (ValidationOverview $validationOverview, HasSchemas $livewire) {
        $validationOverview->description("Please resolve the following errors with this customer");
    })
    ->for([
        EditCompany::class, 
        CreateCompany::class
    ], function (ValidationOverview $validationOverview, HasSchemas $livewire) {
        $validationOverview->description("Please resolve the following errors")
    })
```

The `forAction()` method allows configuration of validation overviews in modals in specific Livewire components. After the Livewire component(s),
specific actions are expected as the second argument.

```php
use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
use Filament\Schemas\Contracts\HasSchemas;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

ValidationOverviewPlugin::make()
    ->forAction(
        EditCustomers::class, 
        'editCustomerInfo', 
        function (ValidationOverview $validationOverview, HasSchemas&HasActions $livewire, Action $action) {
            $validationOverview->description("Please resolve the following errors in this modal");
    })
```

The second argument of `forAction()` that specifies the action scope, can accept a Closure for flexibility.  

```php
use Dvarilek\FilamentValidationOverview\ValidationOverviewPlugin;
use Filament\Schemas\Contracts\HasSchemas;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;

ValidationOverviewPlugin::make()
    ->forAction(
        EditCustomers::class, 
        function (Action $action, HasSchemas&HasActions $livewire) {
            return $action->getName() === 'editCustomerInfo';
        }, 
        function (ValidationOverview $validationOverview) {
            // ...
        })
```

> [!INFO] \
> The `for()`, `forResource()`, `forAction()` and `forResourceAction()` methods can be used multiple times on the plugin instance
> as the specific configurations gets appended to arrays and later resolved. Only one configuration gets applied for each component.

***

### Validation overview configuration

