<?php

declare(strict_types=1);

namespace Dvarilek\FilamentValidationOverview\Components;

use Closure;
use Filament\Schemas\Concerns\BelongsToLivewire;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Components\ViewComponent;
use Livewire\Component;

class ValidationOverview extends ViewComponent
{
    use BelongsToLivewire;
    use Concerns\CanBeHidden;

    protected string $view = 'filament-validation-overview::components.validation-overview';

    protected string|Closure|null $heading = null;

    protected string|Closure|null $description = null;

    protected bool|Closure $isSimple = false;

    /**
     * @param  (Component&HasSchemas)|null  $livewire
     */
    public function __construct(?HasSchemas $livewire = null)
    {
        $this->livewire($livewire);
    }

    /**
     * @param  (Component&HasSchemas)|null  $livewire
     */
    public static function make(?HasSchemas $livewire = null): static
    {
        $static = app(static::class, ['livewire' => $livewire]);
        $static->configure();

        return $static;
    }

    public function heading(string|Closure $heading): static
    {
        $this->heading = $heading;

        return $this;
    }

    public function description(string|Closure $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function simple(bool|Closure $condition = true): static
    {
        $this->isSimple = $condition;

        return $this;
    }

    public function getValidationErrors(): array
    {
        return [

        ];
    }

    public function getHeading(): ?string
    {
        return $this->evaluate($this->heading);
    }

    public function getDescription(): ?string
    {
        return $this->evaluate($this->description);
    }

    public function isSimple(): bool
    {
        return $this->evaluate($this->isSimple);
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'livewire' => [$this->getLivewire()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }

    /**
     * @return array<mixed>
     */
    protected function resolveDefaultClosureDependencyForEvaluationByType(string $parameterType): array
    {
        return match ($parameterType) {
            HasSchemas::class, Component::class => [$this->getLivewire()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByType($parameterType),
        };
    }
}
