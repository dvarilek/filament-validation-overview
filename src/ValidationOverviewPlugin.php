<?php

declare(strict_types=1);

namespace Dvarilek\FilamentValidationOverview;

use Closure;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Contracts\Plugin;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Actions\Concerns\HasAction;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Arr;

class ValidationOverviewPlugin implements Plugin
{
    public static string $name = 'dvarilek/filament-validation-overview';

    /**
     * @var list<array{
     *       components: (class-string<HasSchemas> | list<class-string<HasSchemas>>) | Closure(): (class-string<HasSchemas> | list<class-string<HasSchemas>>),
     *       configureUsing: Closure(ValidationOverview $validationOverview, HasSchemas $livewire): ?ValidationOverview
     *   }>
     */
    protected array $componentConfigurations = [];

    /**
     * @var list<array{
     *       components: (class-string<HasSchemas&HasActions> | list<class-string<HasSchemas&HasActions>>) | Closure(): (class-string<HasSchemas&HasActions> | list<class-string<HasSchemas&HasActions>>),
     *       actions: (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $action): bool),
     *       configureUsing: Closure(ValidationOverview $validationOverview, HasSchemas&HasActions $livewire, Action $action): ?ValidationOverview
     *   }>
     */
    protected array $componentActionConfigurations = [];

    /**
     * @var list<array{
     *       resources: (class-string<Resource> | list<class-string<Resource>>) | Closure(): (class-string<Resource> | list<class-string<Resource>>),
     *       configureUsing: Closure(ValidationOverview $validationOverview, HasSchemas $livewire): ?ValidationOverview
     *   }>
     */
    protected array $resourceConfigurations = [];

    /**
     * @var list<array{
     *       resources: (class-string<Resource> | list<class-string<Resource>>) | Closure(): (class-string<Resource> | list<class-string<Resource>>),
     *       actions: (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $action): bool),
     *       configureUsing: Closure(ValidationOverview $validationOverview, HasSchemas&HasActions $livewire, Action $action): ?ValidationOverview
     *   }>
     */
    protected array $resourceActionConfigurations = [];

    /**
     * @param  (class-string<HasSchemas> | list<class-string<HasSchemas>>) | Closure(): (class-string<HasSchemas> | list<class-string<HasSchemas>>)  $components
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
     * @param  (class-string<HasSchemas&HasActions> | list<class-string<HasSchemas&HasActions>>) | Closure(): (class-string<HasSchemas&HasActions> | list<class-string<HasSchemas&HasActions>>)  $components
     * @param  (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $action): bool)  $actions
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
     * @param  (class-string<Resource> | list<class-string<Resource>>) | Closure(): (class-string<Resource> | list<class-string<Resource>>)  $resources
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
     * @param  (class-string<Resource> | list<class-string<Resource>>) | Closure(): (class-string<Resource> | list<class-string<Resource>>)  $resources
     * @param  (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $action): bool)  $actions
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
        if ($mountedAction) {
            return $this->applyComponentActionConfiguration($validationOverview, $livewire, $mountedAction);
        }

        return $this->applyComponentConfiguration($validationOverview, $livewire);
    }

    protected function applyComponentConfiguration(ValidationOverview $validationOverview, HasSchemas $livewire): ?ValidationOverview
    {
        $livewireClass = $livewire::class;

        foreach ($this->componentConfigurations as $configuration) {
            $components = Arr::wrap($configuration['components'] instanceof Closure ? ($configuration['components'])() : $configuration['components']);
            $configureUsing = $configuration['configureUsing'];

            foreach ($components as $component) {
                if ($component !== $livewireClass) {
                    continue;
                }

                return $configureUsing($validationOverview, $livewire);
            }
        }

        foreach ($this->resourceConfigurations as $configuration) {
            $resources = Arr::wrap($configuration['resources'] instanceof Closure ? ($configuration['resources'])() : $configuration['resources']);
            $configureUsing = $configuration['configureUsing'];

            foreach ($resources as $resource) {
                $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $resource::getPages());

                if (! in_array($livewireClass, $pages)) {
                    continue;
                }

                return $configureUsing($validationOverview, $livewire);
            }
        }

        return null;
    }

    protected function applyComponentActionConfiguration(ValidationOverview $validationOverview, HasSchemas&HasActions $livewire, Action $mountedAction): ?ValidationOverview
    {
        $livewireClass = $livewire::class;

        foreach ($this->componentActionConfigurations as $configuration) {
            $components = Arr::wrap($configuration['components'] instanceof Closure ? ($configuration['components'])() : $configuration['components']);
            $configureUsing = $configuration['configureUsing'];

            foreach ($components as $component) {
                if ($component !== $livewireClass) {
                    continue;
                }

                if ($actions instanceof Closure && $actions($mountedAction) === true) {
                    return $configureUsing($validationOverview, $livewire, $mountedAction);
                }

                foreach (Arr::wrap($actions) as $action) {
                    if ($action !== $mountedAction::class && $action !== $mountedAction->getName()) {
                        continue;
                    }

                    return $configureUsing($validationOverview, $livewire, $mountedAction);
                }
            }
        }

        foreach ($this->resourceActionConfigurations as $configuration) {
            $resources = Arr::wrap($configuration['resources'] instanceof Closure ? ($configuration['resources'])() : $configuration['resources']);
            $configureUsing = $configuration['configureUsing'];

            foreach ($resources as $resource) {
                $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $resource::getPages());

                if (! in_array($livewireClass, $pages)) {
                    continue;
                }

                if ($actions instanceof Closure && $actions($mountedAction) === true) {
                    return $configureUsing($validationOverview, $livewire, $mountedAction);
                }

                foreach (Arr::wrap($actions) as $action) {
                    if ($action !== $mountedAction::class && $action !== $mountedAction->getName()) {
                        continue;
                    }

                    return $configureUsing($validationOverview, $livewire, $mountedAction);
                }
            }
        }

        return null;
    }

    public function register(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::PAGE_HEADER_WIDGETS_AFTER,
            fn () => view('filament-validation-overview::page-validation-overview-wrapper')
        );

        $panel->renderHook(
            PanelsRenderHook::PAGE_FOOTER_WIDGETS_AFTER, // TODO: Change the render hook
            fn () => view('filament-validation-overview::action-validation-overview-wrapper')
        );
    }

    public function boot(Panel $panel): void {}
}
