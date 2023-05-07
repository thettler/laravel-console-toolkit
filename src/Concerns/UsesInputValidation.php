<?php

namespace Thettler\LaravelConsoleToolkit\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Thettler\LaravelConsoleToolkit\Enums\ConsoleInputType;
use Thettler\LaravelConsoleToolkit\Exceptions\ValidationException;
use Thettler\LaravelConsoleToolkit\Reflections\InputReflection;
use Thettler\LaravelConsoleToolkit\Transfers\InputErrorData;

trait UsesInputValidation
{
    /**
     * @param  Collection<InputReflection>  $collection
     * @return Collection<InputReflection>
     * @throws ValidationException
     */
    protected function validate(Collection $collection): Collection
    {
        [
            'values' => $values,
            'rules' => $rules,
            'messages' => $messages,
            'choices' => $choices
        ] = $this->extractValidationData($collection);

        if (empty($rules)) {
            return $collection;
        }

        $validator = Validator::make(
            $values,
            $rules,
            $messages
        );

        if (! $validator->fails()) {
            return $collection;
        }

        $inputErrors = $collection->mapWithKeys(fn (InputReflection $reflection) => [
            $reflection->getName() => new InputErrorData(
                key: $reflection->getName(),
                choices: $choices[$reflection->getName()] ?? [],
                reflection: $reflection,
                hasAutoAsk: $this->hasAutoAskEnabled($reflection),
            ),
        ])->all();

        throw new ValidationException($validator, $inputErrors);
    }

    /**
     * @return array{values:mixed, rules:null|string|array, messages: array}
     */
    protected function extractValidationData(Collection $collection): array
    {
        return $collection->reduce(fn (array $carry, InputReflection $reflection) => [
            'values' => [...$carry['values'], ...$this->extractInputValues($reflection)],
            'rules' => [...$carry['rules'], ...$this->extractInputRules($reflection)],
            'messages' => [...$carry['messages'], ...$this->extractValidationMessages($reflection)],
            'choices' => [...$carry['choices'], ...$this->extractInputChoices($reflection)],
        ], [
            'values' => [],
            'rules' => [],
            'messages' => [],
            'choices' => [],
        ]);
    }

    protected function extractValidationMessages(InputReflection $reflection): array
    {
        if (! $reflection->getValidationMessage()) {
            return [];
        }

        return collect($reflection->getValidationMessage())
            ->mapWithKeys(fn (string $value, string $key) => ["{$reflection->getName()}.{$key}" => $value])
            ->all();
    }

    protected function extractInputValues(
        InputReflection $reflection
    ): array {
        $inputName = $reflection->getAlias() ?? $reflection->getName();

        return [
            $reflection->getName() => match ($reflection::inputType()) {
                ConsoleInputType::Argument => $this->argument($inputName),
                ConsoleInputType::Option => $this->option($inputName),
            },
        ];
    }

    protected function extractInputRules(
        InputReflection $reflection
    ): array {
        $rules = [];

        if ($this->hasAutoAskEnabled($reflection) && ! $reflection->isArray()) {
            $rules[] = 'required';
        }

        if (empty($reflection->getValidationRules())) {
            return empty($rules) ? [] : [$reflection->getName() => $rules];
        }

        return [$reflection->getName() => [...$rules, ...$reflection->getValidationRules()]];
    }

    protected function extractInputChoices(
        InputReflection $reflection
    ): array {
        if (empty($reflection->getChoices())) {
            return [];
        }

        return [$reflection->getName() => $reflection->getChoices()];
    }

    /**
     * @param  InputReflection  $reflection
     * @return bool
     */
    protected function hasAutoAskEnabled(InputReflection $reflection): bool
    {
        return match ($reflection::inputType()) {
            ConsoleInputType::Argument => $reflection->isAutoAskEnabled(),
            ConsoleInputType::Option => $reflection->isAutoAskEnabled()
                && $reflection->hasRequiredValue()
                && array_key_exists($reflection->getName(), $this->getSpecifiedOptions()),
        };
    }

    protected function getSpecifiedOptions(): array
    {
        return (new \ReflectionClass($this->input))->getProperty('options')->getValue($this->input);
    }
}
