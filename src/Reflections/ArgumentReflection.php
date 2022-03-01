<?php

namespace Thettler\LaravelConsoleToolkit\Reflections;

use Thettler\LaravelConsoleToolkit\Attributes\Argument;
use Thettler\LaravelConsoleToolkit\Enums\ConsoleInputType;

class ArgumentReflection extends InputReflection
{
    public static function isArgument(\ReflectionProperty $property): bool
    {
        return ! empty($property->getAttributes(Argument::class));
    }

    public static function inputType(): ConsoleInputType
    {
        return ConsoleInputType::Argument;
    }
}
