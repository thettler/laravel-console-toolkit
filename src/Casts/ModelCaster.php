<?php

namespace Thettler\LaravelConsoleToolkit\Casts;

use Illuminate\Database\Eloquent\Model;
use Thettler\LaravelConsoleToolkit\Contracts\Caster;

class ModelCaster implements Caster
{
    public function __construct(
        protected ?string $findBy = null,
        protected array $select = ['*'],
        protected array $with = []
    ) {
    }

    public function from(mixed $value, string $type, \ReflectionProperty $property): int|float|array|string|bool|null
    {
        return $this->findBy ? $value->{$this->findBy} : $value->getKey();
    }

    /**
     * @param  mixed  $value
     * @param  class-string<Model>  $type
     * @param  \ReflectionProperty  $property
     * @return mixed
     */
    public function to(mixed $value, string $type, \ReflectionProperty $property)
    {
        return $type::where($this->findBy ?? (new $type())->getKeyName(), '=', $value)
            ->with($this->with)
            ->first($this->select);
    }
}
