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

/**
 * @phpstan-type ComponentClass class-string<Page|Resource>
 */
class ValidationOverviewPlugin implements Plugin
{
    public static string $name = 'dvarilek/filament-validation-overview';

    protected bool $inAction = false;

    /**
     * @var bool|Closure(): bool
     */
    protected bool | Closure $isEnabledByDefault = true;

    /**
     * @var list<ComponentClass> | Closure(): list<ComponentClass>
     */
    protected array | Closure $enabledOn = [];

    /**
     * @var list<ComponentClass> | Closure(): list<ComponentClass>
     */
    protected array | Closure $disabledOn = [];

    /**
     * @var list<array{
     *       classes: (ComponentClass | list<ComponentClass>) | Closure(): (ComponentClass | list<ComponentClass>),
     *       configureUsing: Closure(ValidationOverview $validationOverview, Page $page): ?ValidationOverview
     *   }>
     */
    protected array $configurations = [];

    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @param  bool|Closure(): bool  $condition
     */
    public function enabledByDefault(bool | Closure $condition): static
    {
        $this->isEnabledByDefault = $condition;

        return $this;
    }

    /**
     * @param  list<ComponentClass>|Closure(): list<ComponentClass>  $enabledOn
     */
    public function enabledOn(array | Closure $enabledOn): static
    {
        $this->enabledOn = $enabledOn;

        return $this;
    }

    /**
     * @param  list<ComponentClass>|Closure(): list<ComponentClass>  $disabledOn
     */
    public function disabledOn(array | Closure $disabledOn): static
    {
        $this->disabledOn = $disabledOn;

        return $this;
    }

    /**
     * @param  (ComponentClass | list<ComponentClass>) | Closure(): (ComponentClass | list<ComponentClass>)  $classes
     * @param  Closure(ValidationOverview $validationOverview, Page $page): ?ValidationOverview  $configureUsing
     */
    public function for(string | array | Closure $classes, Closure $configureUsing): static
    {
        $this->configurations[] = [
            'classes' => $classes,
            'configureUsing' => $configureUsing,
        ];

        return $this;
    }

    public function forAction(string | array | Closure $classes, string | array | Closure $actions, Closure $configureUsing): static
    {
        $this->configurations[] = [
            'classes' => $classes,
            'actions' => $actions,
            'configureUsing' => $configureUsing,
        ];

        return $this;
    }

    public function getId(): string
    {
        return self::$name;
    }

    public function isEnabledOnPage(Page $page, ?Action $action = null): bool
    {
        $pageClass = $page::class;

        $enabledOn = $this->enabledOn instanceof Closure ? ($this->enabledOn)() : $this->enabledOn;
        $disabledOn = $this->disabledOn instanceof Closure ? ($this->disabledOn)() : $this->disabledOn;
        $isEnabledByDefault = (bool) ($this->isEnabledByDefault instanceof Closure ? ($this->isEnabledByDefault)() : $this->isEnabledByDefault);

        if ($action) {

        }

        foreach ($this->preferPagesOverResources($disabledOn) as $disabled) {
            if ($this->matchesPageClass($pageClass, [$disabled])) {
                return false;
            }
        }

        foreach ($this->preferPagesOverResources($enabledOn) as $enabled) {
            if ($this->matchesPageClass($pageClass, [$enabled])) {
                return true;
            }
        }

        return $isEnabledByDefault;
    }

    public function configureValidationOverview(ValidationOverview $validationOverview, Page $page): ?ValidationOverview
    {
        if (blank($this->configurations)) {
            return null;
        }

        $pageClass = $page::class;

        foreach ($this->configurations as $configuration) {
            $classes = $configuration['classes'] instanceof Closure ? ($configuration['classes'])() : $configuration['classes'];
            $configureUsing = $configuration['configureUsing'];

            if (! is_array($classes)) {
                $classes = [$classes];
            }

            foreach ($this->preferPagesOverResources($classes) as $class) {
                if ($class === $pageClass) {
                    return $configureUsing($validationOverview, $page);
                }

                if (is_subclass_of($class, Resource::class)) {
                    $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $class::getPages());

                    if (in_array($pageClass, $pages)) {
                        return $configureUsing($validationOverview, $page);
                    }
                }

            }
        }

        return null;
    }

    /**
     * @param  class-string<Page>  $pageClass
     * @param  list<ComponentClass>  $classes
     */
    protected function matchesPageClass(string $pageClass, array $classes): bool
    {
        foreach ($this->preferPagesOverResources($classes) as $class) {
            if ($class === $pageClass) {
                return true;
            }

            if (is_subclass_of($class, Resource::class)) {
                $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $class::getPages());

                if (in_array($pageClass, $pages, true)) {
                    return true;
                }
            }
        }

        return false;
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

    public static function get(bool $inAction): static
    {
        return filament(app(static::class, [
            'inAction' => $inAction,
        ])->getId());
    }

    public function register(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::PAGE_HEADER_WIDGETS_AFTER,
            fn () => view('page-validation-overview-wrapper::overview-wrapper')
        );

        $panel->renderHook(
            PanelsRenderHook::PAGE_HEADER_WIDGETS_AFTER, // TODO: Change the render hook
            fn () => view('page-validation-overview-wrapper::overview-wrapper')
        );
    }

    public function boot(Panel $panel): void {}
}
