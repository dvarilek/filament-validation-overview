<?php

declare(strict_types=1);

namespace Dvarilek\FilamentValidationOverview;

use Closure;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\View\ActionsRenderHook;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Arr;

class ValidationOverviewPlugin implements Plugin
{
    public static string $name = 'dvarilek/filament-validation-overview';

    /**
     * @var Closure(ValidationOverview, HasSchemas): ?ValidationOverview | null
     */
    protected ?Closure $defaultConfiguration = null;

    /**
     * @var Closure(ValidationOverview, HasSchemas&HasActions, Action): ?ValidationOverview | null
     */
    protected ?Closure $defaultActionConfiguration = null;

    /**
     * @var bool | Closure(): bool
     */
    protected bool | Closure $isInactive = false;

    /**
     * @var class-string<HasSchemas> | list<class-string<HasSchemas>> | (Closure(HasSchemas): bool)
     */
    protected string | array | Closure $hiddenOnComponents = [];

    /**
     * @var list<array{
     *     components: class-string<HasSchemas&HasActions> | list<class-string<HasSchemas&HasActions>> | (Closure(HasSchemas&HasActions $livewire): bool),
     *     actions: (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $action, HasSchemas&HasActions $livewire): bool)
     * }>
     */
    protected array $hiddenOnComponentActions = [];

    /**
     * @var class-string<resource> | list<class-string<resource>> | (Closure(HasSchemas): bool)
     */
    protected string | array | Closure $hiddenOnResources = [];

    /**
     * @var list<array{
     *     resources: class-string<Resource> | list<class-string<Resource>> | (Closure(HasSchemas $livewire): bool),
     *     actions: (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $action, HasSchemas&HasActions $livewire): bool)
     * }>
     */
    protected array $hiddenOnResourceActions = [];

    /**
     * @var list<array{
     *       components: (class-string<HasSchemas> | list<class-string<HasSchemas>>) | Closure(HasSchemas $livewire): bool,
     *       configureUsing: Closure(ValidationOverview $validationOverview, HasSchemas $livewire): ?ValidationOverview
     *   }>
     */
    protected array $componentConfigurations = [];

    /**
     * @var list<array{
     *       components: (class-string<HasSchemas&HasActions> | list<class-string<HasSchemas&HasActions>>) | Closure(HasSchemas&HasActions $livewire): bool,
     *       actions: (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $action, HasSchemas&HasActions $livewire): bool),
     *       configureUsing: Closure(ValidationOverview $validationOverview, HasSchemas&HasActions $livewire, Action $action): ?ValidationOverview
     *   }>
     */
    protected array $componentActionConfigurations = [];

    /**
     * @var list<array{
     *       resources: (class-string<Resource> | list<class-string<Resource>>) | Closure(HasSchemas $livewire): bool,
     *       configureUsing: Closure(ValidationOverview $validationOverview, HasSchemas $livewire): ?ValidationOverview
     *   }>
     */
    protected array $resourceConfigurations = [];

    /**
     * @var list<array{
     *       resources: (class-string<Resource> | list<class-string<Resource>>) | Closure(HasSchemas $livewire): bool,
     *       actions: (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $action, HasSchemas&HasActions $livewire): bool),
     *       configureUsing: Closure(ValidationOverview $validationOverview, HasSchemas&HasActions $livewire, Action $action): ?ValidationOverview
     *   }>
     */
    protected array $resourceActionConfigurations = [];

    /**
     * @param Closure(ValidationOverview, HasSchemas): ?ValidationOverview | null $configuration
     */
    public function default(?Closure $configuration = null): static
    {
        $this->defaultConfiguration = $configuration;

        return $this;
    }

    /**
     * @param Closure(ValidationOverview, HasSchemas&HasActions, Action): ?ValidationOverview | null $configuration
     */
    public function actionDefault(?Closure $configuration = null): static
    {
        $this->defaultActionConfiguration = $configuration;

        return $this;
    }

    /**
     * @param bool|Closure(): bool $condition
     */
    public function inactive(bool | Closure $condition = true): static
    {
        $this->isInactive = $condition;

        return $this;
    }

    /**
     * @param  class-string<HasSchemas> | list<class-string<HasSchemas>> | (Closure(HasSchemas $livewire): bool)  $components
     */
    public function hiddenOnComponents(string | array | Closure $components): static
    {
        $this->hiddenOnComponents = $components;

        return $this;
    }

    /**
     * @param  class-string<HasSchemas&HasActions> | list<class-string<HasSchemas&HasActions>> | (Closure(HasSchemas&HasActions $livewire): bool)  $components
     * @param  (string|class-string<Action>)|list<string|class-string<Action>>|(Closure(Action, HasSchemas&HasActions): bool)  $actions
     */
    public function hiddenOnComponentActions(string | array | Closure $components, string | array | Closure $actions): static
    {
        $this->hiddenOnComponentActions[] = [
            'components' => $components,
            'actions' => $actions,
        ];

        return $this;
    }

    /**
     * @param  class-string<resource> | list<class-string<resource>> | (Closure(HasSchemas $livewire): bool)  $resources
     */
    public function hiddenOnResources(string | array | Closure $resources): static
    {
        $this->hiddenOnResources = $resources;

        return $this;
    }

    /**
     * @param  class-string<resource> | list<class-string<resource>> | (Closure(HasSchemas $livewire): bool)  $resources
     * @param  (string|class-string<Action>)|list<string|class-string<Action>>|(Closure(Action, HasSchemas&HasActions): bool)  $actions
     */
    public function hiddenOnResourceActions(string | array | Closure $resources, string | array | Closure $actions): static
    {
        $this->hiddenOnResourceActions[] = [
            'resources' => $resources,
            'actions' => $actions,
        ];

        return $this;
    }

    /**
     * @param  (class-string<HasSchemas> | list<class-string<HasSchemas>>) | Closure(HasSchemas $livewire): bool  $components
     * @param  Closure(ValidationOverview $validationOverview, HasSchemas $livewire): ?ValidationOverview  $configureUsing
     */
    public function for(string | array | Closure $components, Closure $configureUsing): static
    {
        $this->componentConfigurations[] = [
            'components' => $components,
            'configureUsing' => $configureUsing,
        ];

        return $this;
    }

    /**
     * @param  (class-string<HasSchemas&HasActions> | list<class-string<HasSchemas&HasActions>>) | Closure(HasSchemas&HasActions $livewire): bool  $components
     * @param  (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $action, HasSchemas&HasActions $livewire): bool)  $actions
     * @param  Closure(ValidationOverview $validationOverview, HasSchemas&HasActions $livewire, Action $action): ?ValidationOverview  $configureUsing
     */
    public function forAction(string | array | Closure $components, string | array | Closure $actions, Closure $configureUsing): static
    {
        $this->componentActionConfigurations[] = [
            'components' => $components,
            'actions' => $actions,
            'configureUsing' => $configureUsing,
        ];

        return $this;
    }

    /**
     * @param  (class-string<resource> | list<class-string<resource>>) | Closure(HasSchemas $livewire): bool  $resources
     * @param  Closure(ValidationOverview $validationOverview, HasSchemas $livewire): ?ValidationOverview  $configureUsing
     */
    public function forResource(string | array | Closure $resources, Closure $configureUsing): static
    {
        $this->resourceConfigurations[] = [
            'resources' => $resources,
            'configureUsing' => $configureUsing,
        ];

        return $this;
    }

    /**
     * @param  (class-string<resource> | list<class-string<resource>>) | Closure(HasSchemas $livewire): bool  $resources
     * @param  (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $action, HasSchemas&HasActions $livewire): bool)  $actions
     * @param  Closure(ValidationOverview $validationOverview, HasSchemas&HasActions $livewire, Action $action): ?ValidationOverview  $configureUsing
     */
    public function forResourceAction(string | array | Closure $resources, string | array | Closure $actions, Closure $configureUsing): static
    {
        $this->resourceActionConfigurations[] = [
            'resources' => $resources,
            'actions' => $actions,
            'configureUsing' => $configureUsing,
        ];

        return $this;
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return self::$name;
    }

    public static function get(): static
    {
        return filament(static::make()->getId());
    }

    public function configureValidationOverview(ValidationOverview $validationOverview, HasSchemas $livewire, ?Action $mountedAction = null): ?ValidationOverview
    {
        if ($mountedAction && $livewire instanceof HasActions) {
            if ($this->isHiddenOnComponentAction($livewire, $mountedAction)) {
                return $validationOverview->hidden();
            }

            $result = $this->applyComponentActionConfiguration($validationOverview, $livewire, $mountedAction);

            if (! $result && $this->canRenderValidationOverview($livewire) && $this->defaultActionConfiguration instanceof Closure) {
                return ($this->defaultActionConfiguration)($validationOverview, $livewire, $mountedAction);
            }

            return $result;
        }

        if ($this->isHiddenOnComponent($livewire)) {
            return $validationOverview->hidden();
        }

        $result = $this->applyComponentConfiguration($validationOverview, $livewire);

        if (! $result && $this->canRenderValidationOverview($livewire) && $this->defaultConfiguration instanceof Closure) {
            return ($this->defaultConfiguration)($validationOverview, $livewire);
        }

        return $result;
    }

    protected function isHiddenOnComponent(HasSchemas $livewire): bool
    {
        $livewireClass = $livewire::class;

        if ($this->hiddenOnComponents instanceof Closure) {
            return ($this->hiddenOnComponents)($livewire) === true;
        }

        $components = Arr::wrap($this->hiddenOnComponents);

        foreach ($components as $component) {
            if ($component === $livewireClass) {
                return true;
            }
        }

        if ($this->hiddenOnResources instanceof Closure) {
            return ($this->hiddenOnResources)($livewire) === true;
        }

        $resources = Arr::wrap($this->hiddenOnResources);

        foreach ($resources as $resource) {
            $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $resource::getPages());

            if (in_array($livewireClass, $pages)) {
                return true;
            }
        }

        return false;
    }

    protected function isHiddenOnComponentAction(HasSchemas & HasActions $livewire, Action $mountedAction): bool
    {
        $livewireClass = $livewire::class;

        foreach ($this->hiddenOnComponentActions as $configuration) {
            $components = $configuration['components'];
            $actions = $configuration['actions'];

            if ($components instanceof Closure) {
                if ($components($livewire) === true) {
                    if ($actions instanceof Closure) {
                        return $actions($mountedAction, $livewire) === true;
                    }

                    foreach (Arr::wrap($actions) as $action) {
                        if ($action === $mountedAction::class || $action === $mountedAction->getName()) {
                            return true;
                        }
                    }
                }

                continue;
            }

            foreach (Arr::wrap($components) as $component) {
                if ($component !== $livewireClass) {
                    continue;
                }

                if ($actions instanceof Closure) {
                    return $actions($mountedAction, $livewire) === true;
                }

                foreach (Arr::wrap($actions) as $action) {
                    if ($action === $mountedAction::class || $action === $mountedAction->getName()) {
                        return true;
                    }
                }
            }
        }

        foreach ($this->hiddenOnResourceActions as $configuration) {
            $resources = $configuration['resources'];
            $actions = $configuration['actions'];

            if ($resources instanceof Closure) {
                if ($resources($livewire) === true) {
                    if ($actions instanceof Closure) {
                        return $actions($mountedAction, $livewire) === true;
                    }

                    foreach (Arr::wrap($actions) as $action) {
                        if ($action === $mountedAction::class || $action === $mountedAction->getName()) {
                            return true;
                        }
                    }
                }

                continue;
            }

            foreach (Arr::wrap($resources) as $resource) {
                $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $resource::getPages());

                if (! in_array($livewireClass, $pages)) {
                    continue;
                }

                if ($actions instanceof Closure) {
                    return $actions($mountedAction, $livewire) === true;
                }

                foreach (Arr::wrap($actions) as $action) {
                    if ($action === $mountedAction::class || $action === $mountedAction->getName()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function applyComponentConfiguration(ValidationOverview $validationOverview, HasSchemas $livewire): ?ValidationOverview
    {
        $args = func_get_args();
        $livewireClass = $livewire::class;

        foreach ($this->componentConfigurations as $configuration) {
            $components = $configuration['components'];
            $configureUsing = $configuration['configureUsing'];

            if ($components instanceof Closure) {
                if ($components($livewire) === true) {
                    return $configureUsing(...$args);
                }

                continue;
            }

            foreach (Arr::wrap($components) as $component) {
                if ($component !== $livewireClass) {
                    continue;
                }

                return $configureUsing(...$args);
            }
        }

        if (! $this->canRenderValidationOverview($livewire)) {
            return null;
        }

        foreach ($this->resourceConfigurations as $configuration) {
            $resources = $configuration['resources'];
            $configureUsing = $configuration['configureUsing'];

            if ($resources instanceof Closure) {
                if ($resources($livewire) === true) {
                    return $configureUsing(...$args);
                }

                continue;
            }

            foreach (Arr::wrap($resources) as $resource) {
                $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $resource::getPages());

                if (! in_array($livewireClass, $pages)) {
                    continue;
                }

                return $configureUsing(...$args);
            }
        }

        return null;
    }

    protected function applyComponentActionConfiguration(ValidationOverview $validationOverview, HasSchemas & HasActions $livewire, Action $mountedAction): ?ValidationOverview
    {
        $args = func_get_args();
        $livewireClass = $livewire::class;

        foreach ($this->componentActionConfigurations as $configuration) {
            $components = $configuration['components'];
            $actions = $configuration['actions'];
            $configureUsing = $configuration['configureUsing'];

            if ($components instanceof Closure) {
                if ($components($livewire) === true) {
                    if ($actions instanceof Closure) {
                        if ($actions($mountedAction, $livewire) === true) {
                            return $configureUsing(...$args);
                        }

                        continue;
                    }

                    foreach (Arr::wrap($actions) as $action) {
                        if ($action === $mountedAction::class || $action === $mountedAction->getName()) {
                            return $configureUsing(...$args);
                        }
                    }
                }

                continue;
            }

            foreach (Arr::wrap($components) as $component) {
                if ($component !== $livewireClass) {
                    continue;
                }

                if ($actions instanceof Closure) {
                    if ($actions($mountedAction, $livewire) === true) {
                        return $configureUsing(...$args);
                    }

                    continue;
                }

                foreach (Arr::wrap($actions) as $action) {
                    if ($action !== $mountedAction::class && $action !== $mountedAction->getName()) {
                        continue;
                    }

                    return $configureUsing(...$args);
                }
            }
        }

        if (! $this->canRenderValidationOverview($livewire)) {
            return null;
        }

        foreach ($this->resourceActionConfigurations as $configuration) {
            $resources = $configuration['resources'];
            $actions = $configuration['actions'];
            $configureUsing = $configuration['configureUsing'];

            if ($resources instanceof Closure) {
                if ($resources($livewire) === true) {
                    if ($actions instanceof Closure) {
                        if ($actions($mountedAction, $livewire) === true) {
                            return $configureUsing(...$args);
                        }

                        continue;
                    }

                    foreach (Arr::wrap($actions) as $action) {
                        if ($action === $mountedAction::class || $action === $mountedAction->getName()) {
                            return $configureUsing(...$args);
                        }
                    }
                }

                continue;
            }

            foreach (Arr::wrap($resources) as $resource) {
                $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $resource::getPages());

                if (! in_array($livewireClass, $pages)) {
                    continue;
                }

                if ($actions instanceof Closure) {
                    if ($actions($mountedAction, $livewire) === true) {
                        return $configureUsing(...$args);
                    }

                    continue;
                }

                foreach (Arr::wrap($actions) as $action) {
                    if ($action !== $mountedAction::class && $action !== $mountedAction->getName()) {
                        continue;
                    }

                    return $configureUsing(...$args);
                }
            }
        }

        return null;
    }

    protected function canRenderValidationOverview(HasSchemas $livewire): bool
    {
        return $livewire instanceof ViewRecord || $livewire instanceof EditRecord;
    }

    public function isInactive(): bool
    {
        return $this->isInactive instanceof Closure ? ($this->isInactive)() : $this->isInactive;
    }

    public function register(Panel $panel): void
    {
        if (! $this->isInactive()) {
            $panel->renderHook(
                PanelsRenderHook::PAGE_HEADER_WIDGETS_AFTER,
                fn () => view('filament-validation-overview::page-validation-overview-wrapper')
            );

            $panel->renderHook(
                ActionsRenderHook::MODAL_SCHEMA_BEFORE,
                fn (Action $action) => view('filament-validation-overview::action-validation-overview-wrapper', [
                    'action' => $action,
                ])
            );
        }
    }

    public function boot(Panel $panel): void {}
}
