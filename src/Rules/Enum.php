<?php

namespace Thettler\LaravelConsoleToolkit\Rules;

use TypeError;

class Enum extends \Illuminate\Validation\Rules\Enum
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (is_null($value) || ! function_exists('enum_exists') || ! enum_exists($this->type)) {
            return false;
        }

        try {
            if (method_exists($this->type, 'tryFrom')) {
                return ! is_null($this->type::tryFrom($value));
            }

            return ! empty(array_filter($this->type::cases(), fn (\UnitEnum $enum) => $enum->name === $value));
        } catch (TypeError $e) {
            return false;
        }
    }
}
