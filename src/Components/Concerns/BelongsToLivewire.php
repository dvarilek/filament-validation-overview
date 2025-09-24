<?php

declare (strict_types=1);

namespace Dvarilek\FilamentValidationOverview\Components\Concerns;

use Closure;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Component;

trait BelongsToLivewire
{
    /**
     * @var (Component & HasSchemas) | null
     */
    protected ?HasSchemas $livewire = null;

    protected string | Closure $schemaName = 'form';

    /**
     * @param  (Component & HasSchemas) | null  $livewire
     */
    public function livewire(?HasSchemas $livewire = null): static
    {
        $this->livewire = $livewire;

        return $this;
    }

    public function schemaName(string | Closure $name): static
    {
        $this->schemaName = $name;

        return $this;
    }

    public function getLivewire(): Component & HasSchemas
    {
        return $this->livewire;
    }

    public function getSchemaName(): string
    {
        return $this->evaluate($this->schemaName);
    }
}
