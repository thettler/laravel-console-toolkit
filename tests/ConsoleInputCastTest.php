<?php

use Illuminate\Console\Command;
use Thettler\LaravelConsoleToolkit\Attributes\Argument;
use Thettler\LaravelConsoleToolkit\Attributes\Option;
use Thettler\LaravelConsoleToolkit\Concerns\UsesConsoleToolkit;
use Thettler\LaravelConsoleToolkit\ConsoleToolkit;
use Thettler\LaravelConsoleToolkit\Contracts\Caster;

class NullCast implements Caster
{
    public function from(mixed $value, string $type, \ReflectionProperty $property): int|float|array|string|bool|null
    {
        return $value . ' fromCast';
    }

    public function to(mixed $value, string $type, \ReflectionProperty $property)
    {
        return $value . ' toCast';
    }
}

$caster = new class () implements Caster {
    public function from(mixed $value, string $type, \ReflectionProperty $property): int|float|array|string|bool|null
    {
        return $value->value;
    }

    public function to(mixed $value, string $type, \ReflectionProperty $property)
    {
        $stdObj = new stdClass();
        $stdObj->value = $value;

        return $stdObj;
    }
};

it('can cast values with class to class notation', function () use ($caster) {
    ConsoleToolkit::addCast($caster::class, stdClass::class);

    $baseCommand = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public stdClass $stdObj;

        #[Argument]
        public stdClass $stdDefaultObj;

        public function configureDefaults()
        {
            $this->stdDefaultObj = new stdClass();
            $this->stdDefaultObj->value = 'Default Value';
        }

        public function handle()
        {
        }
    };


    $command = $this->callCommand($baseCommand, [
        'stdObj' => 'Object Value',
    ]);

    expect($command->stdObj)
        ->toBeInstanceOf(stdClass::class)
        ->toHaveProperty('value', 'Object Value');

    expect($command->stdDefaultObj)
        ->toBeInstanceOf(stdClass::class)
        ->toHaveProperty('value', 'Default Value');
});

it('can cast values with array to class notation', function () use ($caster) {
    ConsoleToolkit::addCast($caster::class, [stdClass::class]);

    $baseCommand = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public stdClass $stdObj;

        #[Argument]
        public stdClass $stdDefaultObj;

        public function configureDefaults()
        {
            $this->stdDefaultObj = new stdClass();
            $this->stdDefaultObj->value = 'Default Value';
        }

        public function handle()
        {
        }
    };


    $command = $this->callCommand($baseCommand, [
        'stdObj' => 'Object Value',
    ]);

    expect($command->stdObj)
        ->toBeInstanceOf(stdClass::class)
        ->toHaveProperty('value', 'Object Value');

    expect($command->stdDefaultObj)
        ->toBeInstanceOf(stdClass::class)
        ->toHaveProperty('value', 'Default Value');
});

it('can set casts on console input ', function () {
    $baseCommand = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument(
            cast: NullCast::class
        )]
        public string $castArgument = 'default';

        #[Option(
            cast: NullCast::class
        )]
        public string $castOption = 'default';

        public function handle()
        {
        }
    };

    $definition = $baseCommand->getDefinition();

    $command = $this->callCommand($baseCommand, [
        'castArgument' => 'Value',
        '--castOption' => 'Value',
    ]);

    expect($definition->getArgument('castArgument')->getDefault())->toBe('default fromCast');
    expect($definition->getOption('castOption')->getDefault())->toBe('default fromCast');

    expect($command->castArgument)
        ->toBe('Value toCast');

    expect($command->castOption)
        ->toBe('Value toCast');
});

it('can set casts as object on console input ', function () {
    $baseCommand = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument(
            cast: new NullCast()
        )]
        public string $castArgument = 'default';

        #[Option(
            cast: new NullCast()
        )]
        public string $castOption = 'default';

        public function handle()
        {
        }
    };

    $definition = $baseCommand->getDefinition();

    $command = $this->callCommand($baseCommand, [
        'castArgument' => 'Value',
        '--castOption' => 'Value',
    ]);

    expect($definition->getArgument('castArgument')->getDefault())->toBe('default fromCast');
    expect($definition->getOption('castOption')->getDefault())->toBe('default fromCast');

    expect($command->castArgument)
        ->toBe('Value toCast');

    expect($command->castOption)
        ->toBe('Value toCast');
});
