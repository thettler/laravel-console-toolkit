<?php

namespace Thettler\LaravelConsoleToolkit\Casts;

use Thettler\LaravelConsoleToolkit\Contracts\Caster;

class EnumCaster implements Caster
{
    /**
     * @param  class-string|null  $enum
     */
    public function __construct(
        protected ?string $enum = null
    ) {
    }

    public function from(mixed $value, string $type, \ReflectionProperty $property): int|float|array|string|bool|null
    {
        return (new \ReflectionEnum($value))->isBacked()
            ? $value->value
            : $value->name;
    }

    public function to(mixed $value, string $type, \ReflectionProperty $property)
    {
        $enumName = $this->getEnumName($type);

        if (! $enumName) {
            return $value;
        }

        if (! enum_exists($enumName)) {
            return $value;
        }

        $enum = new \ReflectionEnum($enumName);

        return $enum->isBacked()
            ? ($enumName)::from((string) $value)
            : $enum->getCase((string) $value)->getValue();
    }

    protected function getEnumName(string $type): ?string
    {
        if ($this->enum) {
            return $this->enum;
        }

        return $type;
    }
}
