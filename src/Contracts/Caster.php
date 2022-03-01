<?php

namespace Thettler\LaravelConsoleToolkit\Contracts;

interface Caster
{
    /**
     * Cast a value from this type to a scalar type
     *
     * @return int|float|array|string|bool|null
     */
    public function from(mixed $value, string $type, \ReflectionProperty $property): int|float|array|string|bool|null;

    /**
     * Cast a value to a type
     *
     * @return mixed
     */
    public function to(mixed $value, string $type, \ReflectionProperty $property);
}
