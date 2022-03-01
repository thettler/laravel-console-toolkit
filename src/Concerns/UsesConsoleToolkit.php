<?php

namespace Thettler\LaravelConsoleToolkit\Concerns;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Thettler\LaravelConsoleToolkit\Enums\ConsoleInputType;
use Thettler\LaravelConsoleToolkit\Exceptions\ValidationException;
use Thettler\LaravelConsoleToolkit\Reflections\ArgumentReflection;
use Thettler\LaravelConsoleToolkit\Reflections\CommandReflection;
use Thettler\LaravelConsoleToolkit\Reflections\OptionReflection;
use Thettler\LaravelConsoleToolkit\Transfers\InputErrorData;

trait UsesConsoleToolkit
{
    use UsesInputValidation;

    protected CommandReflection $reflection;

    public function __construct()
    {
        $this->configureDefaults();
        parent::__construct();
    }

    public function specifyParameters()
    {
        $this->reflection = new CommandReflection($this);

        if ($this->reflection->usesCommandAttribute()) {
            SymfonyCommand::__construct($this->name = $this->reflection->getName());
            $this->setDescription($this->reflection->getDescription());
            $this->setHelp($this->reflection->getHelp());
            $this->setHidden($this->reflection->isHidden());
            $this->setAliases($this->reflection->getAliases());
        }

        parent::specifyParameters();
    }

    public function configureDefaults(): void
    {
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        if (! $this->reflection->usesInputAttributes()) {
            return [];
        }

        return $this->reflection
            ->getArguments()
            ->map(fn (ArgumentReflection $argumentReflection) => $this->propertyToArgument($argumentReflection))
            ->all();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        if (! $this->reflection->usesInputAttributes()) {
            return [];
        }

        return $this->reflection
            ->getOptions()
            ->map(fn (OptionReflection $optionReflection) => $this->propertyToOption($optionReflection))
            ->all();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hasErrors = ! $this->errorHandling(fn () => $this->hydrateArguments());
        $hasErrors = ! $this->errorHandling(fn () => $this->hydrateOptions()) || $hasErrors;

        if ($hasErrors) {
            return SymfonyCommand::FAILURE;
        }

        return parent::execute($input, $output);
    }

    protected function errorHandling(callable $callable): bool
    {
        try {
            $callable();

            return true;
        } catch (ValidationException $validationException) {
            $rerun = false;
            foreach ($validationException->validator->errors()->toArray() as $key => $errors) {
                $inputErrorData = $validationException->inputs[$key];

                $this->renderDivider($inputErrorData);
                $this->renderValidationErrors($inputErrorData, $errors);

                if ($inputErrorData->hasAutoAsk) {
                    $answer = ! empty($inputErrorData->choices)
                        ? $this->renderAutoAskChoice($inputErrorData)
                        : $this->renderAutoAsk($inputErrorData);

                    match ($inputErrorData->reflection::inputType()) {
                        ConsoleInputType::Argument => $this->input->setArgument($key, $answer),
                        ConsoleInputType::Option => $this->input->setOption($key, $answer),
                    };

                    $rerun = true;

                    continue;
                }

                if (! empty($inputErrorData->choices)) {
                    $this->renderChoiceOptions($inputErrorData);
                }
            }

            if ($rerun) {
                return $this->errorHandling($callable);
            }

            return false;
        }
    }

    protected function hydrateArguments(): void
    {
        $this->reflection
            ->getArguments()
            ->pipeThrough(
                fn (Collection $collection) => $this->validate($collection),
            )
            ->each(function (ArgumentReflection $argumentReflection) {
                $this->{$argumentReflection->getName()} = $argumentReflection->castTo(
                    $this->argument($argumentReflection->getAlias() ?? $argumentReflection->getName())
                );
            });
    }

    protected function hydrateOptions(): void
    {
        $this->reflection
            ->getOptions()
            ->pipeThrough(fn (Collection $collection) => $this->validate($collection))
            ->each(function (OptionReflection $optionReflection) {
                $consoleName = $optionReflection->getAlias() ?? $optionReflection->getName();
                if (! $optionReflection->hasRequiredValue()) {
                    $this->{$optionReflection->getName()} = $optionReflection->castTo($this->option($consoleName));

                    return;
                }

                if ($this->option($consoleName) === null) {
                    return;
                }

                $this->{$optionReflection->getName()} = $optionReflection->castTo($this->option($consoleName));
            });
    }

    protected function propertyToArgument(ArgumentReflection $argument): InputArgument
    {
        return match (true) {
            $argument->isArray() && ! $argument->isOptional() => $this->makeInputArgument(
                $argument,
                $argument->isAutoAskEnabled() ? InputArgument::IS_ARRAY | InputArgument::OPTIONAL : InputArgument::IS_ARRAY | InputArgument::REQUIRED
            ),

            $argument->isArray() => $this->makeInputArgument(
                $argument,
                InputArgument::IS_ARRAY,
                $argument->getDefaultValue()
            ),

            $argument->isOptional() || $argument->getDefaultValue() => $this->makeInputArgument(
                $argument,
                InputArgument::OPTIONAL,
                $argument->getDefaultValue()
            ),

            default => $this->makeInputArgument(
                $argument,
                $argument->isAutoAskEnabled() ? InputArgument::OPTIONAL : InputArgument::REQUIRED
            ),
        };
    }

    protected function propertyToOption(OptionReflection $option): InputOption
    {
        return match (true) {
            $option->hasValue() && $option->isArray() => $this->makeInputOption(
                $option,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                $option->getDefaultValue()
            ),

            $option->hasRequiredValue() => $this->makeInputOption(
                $option,
                $option->isAutoAskEnabled() ? InputOption::VALUE_OPTIONAL : InputOption::VALUE_REQUIRED,
                $option->isAutoAskEnabled() ? '$__not_provided__$' : null
            ),

            $option->hasValue() => $this->makeInputOption(
                $option,
                InputOption::VALUE_OPTIONAL,
                $option->getDefaultValue()
            ),

            $option->isNegatable() => $this->makeInputOption(
                $option,
                InputOption::VALUE_NEGATABLE,
                $option->getDefaultValue() !== null ? $option->getDefaultValue() : false
            ),

            default => $this->makeInputOption($option, InputOption::VALUE_NONE),
        };
    }

    protected function makeInputArgument(
        ArgumentReflection $argument,
        ?int $mode,
        string|bool|int|float|array|null $default = null
    ): InputArgument {
        return new InputArgument(
            $argument->getAlias() ?? $argument->getName(),
            $mode,
            $argument->getDescription(),
            $default
        );
    }

    protected function makeInputOption(
        OptionReflection $option,
        ?int $mode,
        string|bool|int|float|array|null $default = null
    ): InputOption {
        return new InputOption(
            $option->getAlias() ?? $option->getName(),
            $option->getShortcut(),
            $mode,
            $option->getDescription(),
            $default
        );
    }

    protected function renderChoiceOptions(InputErrorData $inputErrorData): void
    {
        $this->info("Possible values for: {$inputErrorData->key}.");

        foreach ($inputErrorData->choices as $choice) {
            $this->warn("   - {$choice}");
        }
    }

    protected function renderAutoAsk(InputErrorData $inputErrorData): mixed
    {
        return $this->ask('Please enter "'.$inputErrorData->key.'"');
    }

    protected function renderAutoAskChoice(InputErrorData $inputErrorData): string|array
    {
        return $this->choice(
            'Please enter "'.$inputErrorData->key.'"',
            $inputErrorData->choices,
            $inputErrorData->reflection->getDefaultValue(),
            null,
            $inputErrorData->reflection->isArray()
        );
    }

    protected function renderValidationErrors(InputErrorData $inputErrorData, array $errors): void
    {
        foreach ($errors as $error) {
            $this->error($error);
        }
    }

    protected function renderDivider(InputErrorData $inputErrorData): void
    {
        $this->line(" ");
    }
}
