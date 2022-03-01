<?php

namespace Thettler\LaravelConsoleToolkit\Attributes;

use Thettler\LaravelConsoleToolkit\Contracts\Caster;
use Thettler\LaravelConsoleToolkit\Contracts\ConsoleInput;
use Thettler\LaravelConsoleToolkit\Transfers\Validation;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Option implements ConsoleInput
{
    /**
     * @param  string  $description
     * @param  string|null $as
     * @param  class-string<Caster>|Caster|null  $cast
     * @param  string|null  $shortcut
     * @param  bool  $negatable
     */
    public function __construct(
        protected string $description = '',
        protected ?string $as = null,
        protected null|Caster|string $cast = null,
        protected ?string $shortcut = null,
        protected bool $negatable = false,
        protected null|string|array|Validation $validation = null,
        protected ?bool $autoAsk = null,
    ) {
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getAlias(): ?string
    {
        return $this->as;
    }

    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }

    public function isNegatable(): bool
    {
        return $this->negatable;
    }

    public function getCast(): null|Caster|string
    {
        return $this->cast;
    }

    public function hasAutoAsk(): ?bool
    {
        return $this->autoAsk;
    }

    public function getValidation(): null|array|string|Validation
    {
        return $this->validation;
    }
}
