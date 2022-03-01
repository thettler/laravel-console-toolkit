<?php

namespace Thettler\LaravelConsoleToolkit\Casts;

use Illuminate\Support\Arr;
use Thettler\LaravelConsoleToolkit\Contracts\Caster;

class ArrayCaster implements Caster
{
    /**
     * @param  Caster|class-string<Caster>  $caster
     */
    public function __construct(
        protected Caster|string $caster,
        protected string $type,
    ) {
    }

    /**
     * @param  int|float|array|string|bool|null  $value
     * @param  string  $type
     * @param  \ReflectionProperty  $property
     * @return int|float|array|string|bool|null
     */
    public function from(mixed $value, string $type, \ReflectionProperty $property): int|float|array|string|bool|null
    {
        $value = Arr::wrap($value);

        return collect($value)
            ->map(function (mixed $item) use ($property) {
                return $this->getItemCaster()->from($item, $this->type, $property);
            })
            ->all();
    }

    public function to(mixed $value, string $type, \ReflectionProperty $property)
    {
        $value = Arr::wrap($value);

        return collect($value)
            ->map(fn ($item) => $this->getItemCaster()->to($item, $this->type, $property))
            ->all();
    }

    protected function getItemCaster(): Caster
    {
        if (is_string($this->caster)) {
            return app()->make($this->caster);
        }

        return $this->caster;
    }
}
