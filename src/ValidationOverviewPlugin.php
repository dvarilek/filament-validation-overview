<?php

declare(strict_types=1);

namespace Dvarilek\FilamentValidationOverview;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\Resource;
use Filament\View\PanelsRenderHook;

class ValidationOverviewPlugin implements Plugin
{
    public static string $name = 'dvarilek/filament-validation-overview';

    /**
     * @var bool|Closure(): bool
     */
    protected bool | Closure $isEnabledByDefault = true;

    /**
     * @var array<class-string<Page|resource>> | Closure(): array<class-string<Page|resource>>
     */
    protected array | Closure $enabledOn = [];

    /**
     * @var array<class-string<Page|resource>> | Closure(): array<class-string<Page|resource>>
     */
    protected array | Closure $disabledOn = [];

    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @param  bool|Closure(): bool  $enabled
     */
    public function enabledByDefault(bool | Closure $condition): static
    {
        $this->isEnabledByDefault = $condition;

        return $this;
    }

    /**
     * @param  array<class-string<Page|resource>>|Closure(): array<class-string<Page|resource>>  $enabledOn
     */
    public function enabledOn(array | Closure $enabledOn): static
    {
        $this->enabledOn = $enabledOn;

        return $this;
    }

    /**
     * @param  array<class-string<Page|resource>>|Closure(): array<class-string<Page|resource>>  $disabledOn
     */
    public function disabledOn(array | Closure $disabledOn): static
    {
        $this->disabledOn = $disabledOn;

        return $this;
    }

    public function getId(): string
    {
        return self::$name;
    }

    /**
     * @param  class-string<Page>  $page
     */
    public function isEnabledOn(string $page): bool
    {
        $enabledOn = $this->enabledOn instanceof Closure ? ($this->enabledOn)() : $this->enabledOn;
        $disabledOn = $this->disabledOn instanceof Closure ? ($this->disabledOn)() : $this->disabledOn;
        $isEnabledByDefault = (bool) ($this->isEnabledByDefault instanceof Closure ? ($this->isEnabledByDefault)() : $this->isEnabledByDefault);

        foreach ($disabledOn ?? [] as $disabled) {
            if ($disabled === $page) {
                return false;
            }

            if (is_subclass_of($disabled, Resource::class)) {
                $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $disabled::getPages());

                if (in_array($page, $pages, true)) {
                    return false;
                }
            }
        }

        foreach ($enabledOn ?? [] as $enabled) {
            if ($enabled === $page) {
                return true;
            }

            if (is_subclass_of($enabled, Resource::class)) {
                $pages = array_map(static fn (PageRegistration $pageRegistration) => $pageRegistration->getPage(), $enabled::getPages());

                if (in_array($page, $pages, true)) {
                    return true;
                }
            }
        }

        return $isEnabledByDefault;
    }

    public function register(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::PAGE_HEADER_WIDGETS_AFTER,
            fn () => view('filament-validation-overview::validation-overview')
        );
    }

    public function boot(Panel $panel): void {}
}
