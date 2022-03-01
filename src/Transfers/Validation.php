<?php

namespace Thettler\LaravelConsoleToolkit\Transfers;

class Validation
{
    public function __construct(
        public readonly array | string $rules,
        public readonly array $messages,
    ) {
    }
}
