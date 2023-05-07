<?php

namespace Thettler\LaravelConsoleToolkit\Transfers;

use Thettler\LaravelConsoleToolkit\Contracts\ConsoleInput;
use Thettler\LaravelConsoleToolkit\Reflections\InputReflection;

class InputErrorData
{
    /**
     * @param  string  $key
     * @param  array  $choices
     * @param  InputReflection<ConsoleInput>  $reflection
     * @param  bool  $hasAutoAsk
     */
    public function __construct(
        public readonly string $key,
        public readonly array $choices,
        public readonly InputReflection $reflection,
        public readonly bool $hasAutoAsk,
    ) {
    }
}
