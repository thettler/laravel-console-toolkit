<?php

namespace Thettler\LaravelConsoleToolkit\Contracts;

use Thettler\LaravelConsoleToolkit\Transfers\Validation;

interface ConsoleInput
{
    public function getDescription(): string;

    public function getAlias(): ?string;

    public function getCast(): null|Caster|string;

    public function getValidation(): null|array|string|Validation;

    public function hasAutoAsk(): ?bool;
}
