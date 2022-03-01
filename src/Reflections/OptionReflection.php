<?php

namespace Thettler\LaravelConsoleToolkit\Reflections;

use Illuminate\Console\Command;
use Thettler\LaravelConsoleToolkit\Attributes\Option;
use Thettler\LaravelConsoleToolkit\Enums\ConsoleInputType;

/**
 * @extends InputReflection<Option>
 */
class OptionReflection extends InputReflection
{
    public function __construct(\ReflectionProperty $property, Option $consoleInput, Command $commandReflection)
    {
        parent::__construct($property, $consoleInput, $commandReflection);
    }

    public static function isOption(\ReflectionProperty $property): bool
    {
        return ! empty($property->getAttributes(Option::class));
    }

    public function isNegatable(): bool
    {
        return $this->consoleInput->isNegatable();
    }

    public function hasRequiredValue(): bool
    {
        return $this->hasValue() && ! $this->isOptional();
    }

    public function getShortcut(): ?string
    {
        return $this->consoleInput->getShortcut();
    }

    public function hasValue(): bool
    {
        if (($type = $this->property->getType()) instanceof \ReflectionNamedType) {
            return $type->getName() !== 'bool';
        }

        return false;
    }

    public function isAutoAskEnabled(): bool
    {
        return $this->hasRequiredValue() && parent::isAutoAskEnabled();
    }

    public static function inputType(): ConsoleInputType
    {
        return ConsoleInputType::Option;
    }
}
