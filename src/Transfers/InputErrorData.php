<?php

namespace Thettler\LaravelConsoleToolkit\Transfers;

use Thettler\LaravelConsoleToolkit\Reflections\InputReflection;

class InputErrorData
{
    public function __construct(
        public readonly string $key,
        public readonly array $choices,
        public readonly InputReflection $reflection,
        public readonly bool $hasAutoAsk,
    ) {
    }
}
