<?php

declare(strict_types=1);

namespace Dvarilek\FilamentValidationOverview;

use Closure;
use Dvarilek\FilamentValidationOverview\Components\ValidationOverview;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Arr;

/**
 * @phpstan-type ComponentClass class-string<Page|Resource>
 */
class ValidationOverviewPlugin implements Plugin
{
    public static string $name = 'dvarilek/filament-validation-overview';

    /**
     * @var list<array{
     *       classes: (ComponentClass | list<ComponentClass>) | Closure(): (ComponentClass | list<ComponentClass>),
     *       actions?: (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $mountedAction): bool),
     *       configureUsing: Closure(ValidationOverview $validationOverview, Page $page, ?Action $action): ?ValidationOverview
     *   }>
     */
    protected array $configurations = [];

    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @param  (ComponentClass | list<ComponentClass>) | Closure(): (ComponentClass | list<ComponentClass>)  $classes
     * @param  Closure(ValidationOverview $validationOverview, Page $page, ?Action $action): ?ValidationOverview  $configureUsing
     */
    public function for(string | array | Closure $classes, Closure $configureUsing): static
    {
        $this->configurations[] = [
            'classes' => $classes,
            'configureUsing' => $configureUsing,
        ];

        return $this;
    }

    /**
     * @param  (ComponentClass | list<ComponentClass>) | Closure(): (ComponentClass | list<ComponentClass>)  $classes
     * @param  (string | class-string<Action>) | list<string | class-string<Action>> | (Closure(Action $mountedAction): bool)  $actions
     * @param  Closure(ValidationOverview $validationOverview, Page $page, ?Action $action): ?ValidationOverview  $configureUsing
     */
    public function forAction(string | array | Closure $classes, string | array | Closure $actions, Closure $configureUsing): static
    {
        $this->configurations[] = [
            'classes' => $classes,
            'actions' => $actions,
            'configureUsing' => $configureUsing,
        ];

        return $this;
    }

    public function configureValidationOverview(ValidationOverview $validationOverview, Page $page, ?Action $mountedAction = null): ?ValidationOverview
    {
        if (blank($this->configurations)) {
            return null;
        }

        $isConfigured = false;

        $configureValidationOverviewForPage = static function (array $classes, Closure $configureUsing) use ($validationOverview, $page, $mountedAction, &$isConfigured): ?ValidationOverview {
            $pageClass = $page::class;

            /* @phpstan-ignore-next-line */
            foreach ($classes as $class) {
                if ($class === $pageClass) {
                    $isConfigured = true;

                    return $configureUsing($validationOverview, $page, $mountedAction);
                }

                if (is_subclass_of($class, Resource::class)) {
                    $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $class::getPages());

                    if (in_array($pageClass, $pages)) {
                        $isConfigured = true;

                        return $configureUsing($validationOverview, $page, $mountedAction);
                    }
                }

            }

            return null;
        };

        foreach ($this->configurations as $configuration) {
            if ($isConfigured) {
                break;
            }

            $classes = Arr::wrap($configuration['classes'] instanceof Closure ? ($configuration['classes'])() : $configuration['classes']);
            $configureUsing = $configuration['configureUsing'];

            if ($mountedAction && (($actions = ($configuration['actions'] ?? null)))) {
                if ($actions instanceof Closure) {
                    if ($actions($mountedAction) === true) {
                        $validationOverview = $configureValidationOverviewForPage($classes, $configureUsing);
                    }

                    continue;
                }

                foreach (Arr::wrap($actions) as $action) {
                    if ($action !== $mountedAction::class && $action !== $mountedAction->getName()) {
                        continue;
                    }

                    $validationOverview = $configureValidationOverviewForPage($classes, $configureUsing);
                }

                continue;
            }

            $validationOverview = $configureValidationOverviewForPage($classes, $configureUsing);
        }

        return $validationOverview;
    }

    /**
     * @param  list<ComponentClass>  $classes
     * @return list<ComponentClass>
     */
    protected function preferPagesOverResources(array $classes): array
    {
        $partitionedClasses = array_reduce($classes, function (array $carry, string $class) {
            if (is_subclass_of($class, Page::class)) {
                $carry['pages'][] = $class;
            } elseif (is_subclass_of($class, Resource::class)) {
                $carry['resources'][] = $class;
            }

            return $carry;
        }, ['pages' => [], 'resources' => []]);

        /**
         * @var list<ComponentClass>
         */
        return [...$partitionedClasses['pages'], ...$partitionedClasses['resources']];
    }

    public function getId(): string
    {
        return self::$name;
    }

    public static function get(): static
    {
        return filament(static::make()->getId());
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
