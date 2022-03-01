<?php

namespace Thettler\LaravelConsoleToolkit\Reflections;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Thettler\LaravelConsoleToolkit\ConsoleToolkit;
use Thettler\LaravelConsoleToolkit\Contracts\Caster;
use Thettler\LaravelConsoleToolkit\Contracts\ConsoleInput;
use Thettler\LaravelConsoleToolkit\Enums\ConsoleInputType;
use Thettler\LaravelConsoleToolkit\Exceptions\InvalidTypeException;
use Thettler\LaravelConsoleToolkit\Rules\Enum;
use Thettler\LaravelConsoleToolkit\Transfers\Validation;

/**
 * @template T of ConsoleInput
 */
abstract class InputReflection
{
    protected string $type;

    /**
     * @param  \ReflectionProperty  $property
     * @param  T  $consoleInput
     * @param  Command  $command
     * @throws InvalidTypeException
     */
    public function __construct(
        protected \ReflectionProperty $property,
        protected ConsoleInput $consoleInput,
        protected Command $command
    ) {
        if (! ($type = $this->property->getType())) {
            throw new InvalidTypeException("A type is required for the console input \"{$this->property->getName()}\".");
        }

        if (! $type instanceof \ReflectionNamedType) {
            throw new InvalidTypeException("Only named types can be used for the console input \"{$this->property->getName()}\".");
        }

        $this->type = $type;
    }

    abstract public static function inputType(): ConsoleInputType;

    public function getName(): string
    {
        return $this->property->getName();
    }

    public function getAlias(): ?string
    {
        return $this->consoleInput->getAlias();
    }

    public function getValidationRules(): array
    {
        $autoRules = [];

        if (enum_exists($this->type)) {
            $autoRules[] = new Enum($this->type);
        }

        $rules = $this->consoleInput->getValidation() instanceof Validation
            ? $this->consoleInput->getValidation()->rules
            : $this->consoleInput->getValidation();

        return [...$autoRules, ...Arr::wrap($rules)];
    }

    public function getValidationMessage(): null|array
    {
        return $this->consoleInput->getValidation() instanceof Validation
            ? $this->consoleInput->getValidation()->messages
            : null;
    }

    public function getChoices(): array
    {
        if (! enum_exists($this->type)) {
            return [];
        }

        return array_map(
            fn (\UnitEnum|\BackedEnum $enum) => $enum instanceof \BackedEnum ? $enum->value : $enum->name,
            $this->type::cases()
        );
    }

    public function getDescription(): string
    {
        return $this->consoleInput->getDescription();
    }

    public function getDefaultValue(): string|bool|int|float|array|null
    {
        return $this->property->hasDefaultValue() || $this->property->isInitialized($this->command)
            ? $this->castFrom()
            : null;
    }

    public function isOptional(): bool
    {
        return $this->property->hasDefaultValue()
            || $this->property->getType()?->allowsNull()
            || $this->property->isInitialized($this->command);
    }

    public function isAutoAskEnabled(): bool
    {
        if ($this->consoleInput->hasAutoAsk() !== null) {
            return $this->consoleInput->hasAutoAsk();
        }

        return ConsoleToolkit::$hasAutoAskEnabled;
    }

    public function isArray(): bool
    {
        if (($type = $this->property->getType()) instanceof \ReflectionNamedType) {
            return $type->getName() === 'array';
        }

        return false;
    }

    public function castFrom(): int|float|array|string|bool|null
    {
        $value = $this->property->isInitialized($this->command)
            ? $this->property->getValue($this->command)
            : $this->property->getDefaultValue();

        $caster = $this->getCaster($value, $this->property);

        if (! $caster) {
            return $value;
        }

        return $caster->from($value, $this->type, $this->property);
    }

    public function castTo(int|array|float|string|bool|null $value): mixed
    {
        $caster = $this->getCaster($value, $this->property);

        if (! $caster) {
            return $value;
        }

        return $caster->to($value, $this->type, $this->property);
    }

    protected function getCaster(mixed $value, \ReflectionProperty $property): ?Caster
    {
        if ($cast = $this->consoleInput->getCast()) {
            return is_string($cast) ? app()->make($cast) : $cast;
        }

        $casterString = collect(ConsoleToolkit::$casts)
            ->filter(function (callable|string|array $matcher) use ($value, $property) {
                if (is_callable($matcher)) {
                    return $matcher($value, $property);
                }

                if (is_string($matcher)) {
                    return $matcher === $this->type;
                }

                if (! is_array($matcher)) {
                    return false;
                }

                foreach ($matcher as $match) {
                    if ($match !== $this->type) {
                        continue;
                    }

                    return true;
                }

                return false;
            })
            ->keys()
            ->first();


        if (! $casterString) {
            return null;
        }

        return app()->make($casterString);
    }
}
