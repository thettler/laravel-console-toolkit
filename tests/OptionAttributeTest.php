<?php

use Illuminate\Console\Command;
use Thettler\LaravelConsoleToolkit\Attributes\Option;
use Thettler\LaravelConsoleToolkit\Concerns\UsesConsoleToolkit;
use Thettler\LaravelConsoleToolkit\ConsoleToolkit;

it('Options Will Be Registered With Attribute Syntax', function () {
    ConsoleToolkit::enableAutoAsk(false);

    $baseCommand = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Option]
        public bool $option;

        #[Option]
        public string $optionWithValue;

        #[Option]
        public ?string $optionWithNullableValue;

        #[Option]
        public string $optionWithDefaultValue = 'default';

        public function handle()
        {
        }
    };

    $definition = $baseCommand->getDefinition();

    $command = $this->callCommand($baseCommand, [
        '--option' => true,
        '--optionWithValue' => 'Value A',
    ]);

    $this->assertFalse($definition->getOption('option')->isValueOptional());
    $this->assertTrue($command->option);

    $this->assertTrue($definition->getOption('optionWithValue')->isValueRequired());
    $this->assertSame('Value A', $command->optionWithValue);

    $this->assertTrue($definition->getOption('optionWithNullableValue')->isValueOptional());
    $this->assertNull($command->optionWithNullableValue);

    $command = $this->callCommand($baseCommand, [
        '--optionWithNullableValue' => 'Value B',
    ]);
    $this->assertSame('Value B', $command->optionWithNullableValue);

    $this->assertSame('default', $definition->getOption('optionWithDefaultValue')->getDefault());

    $command = $this->callCommand($baseCommand, [
        '--optionWithDefaultValue' => 'Value C',
    ]);
    $this->assertSame('Value C', $command->optionWithDefaultValue);
});

it('Array Options Will Be Registered With Attribute Syntax', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Option]
        public array $optionArray;

        #[Option]
        public array $optionDefaultArray = ['default1', 'default2'];

        public function handle()
        {
        }
    };

    $definition = $command->getDefinition();

    $command = $this->callCommand($command, []);
    $this->assertSame([], $command->optionArray);

    $command = $this->callCommand($command, [
        '--optionArray' => ['Value A', 'Value B'],
    ]);

    $this->assertTrue($definition->getOption('optionArray')->isArray());
    $this->assertSame(['Value A', 'Value B'], $command->optionArray);

    $this->assertTrue($definition->getOption('optionArray')->isArray());
    $this->assertTrue($definition->getOption('optionArray')->isValueOptional());
    $this->assertSame(['Value A', 'Value B'], $command->optionArray);

    $command = $this->callCommand($command, [
        '--optionDefaultArray' => ['Value C', 'Value D'],
    ]);

    $this->assertSame(['Value C', 'Value D'], $command->optionDefaultArray);
});

it('can describe option input', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Option(
            description: 'Option Description',
            as: 'optionAlias'
        )]
        public bool $option;

        public function handle()
        {
        }
    };

    $definition = $command->getDefinition();

    $this->assertSame('Option Description', $definition->getOption('optionAlias')->getDescription());

    $command = $this->callCommand($command, [
        '--optionAlias' => true,
    ]);

    $this->assertSame(true, $command->option);
});

it('can use a option shortcut', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Option(
            shortcut: 'O'
        )]
        public string $option;

        public function handle()
        {
        }
    };

    $definition = $command->getDefinition();

    $this->assertSame('O', $definition->getOption('option')->getShortcut());

    $command = $this->callCommand($command, [
        '-O' => 'short',
    ]);

    $this->assertSame('short', $command->option);
});

it('can use a negatable option', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Option(
            negatable: true
        )]
        public bool $option;

        public function handle()
        {
        }
    };

    $definition = $command->getDefinition();

    $this->assertTrue($definition->getOption('option')->isNegatable());

    $command = $this->callCommand($command, [
        '--option' => true,
    ]);

    $this->assertTrue($command->option);

    $command = $this->callCommand($command, [
        '--no-option' => true,
    ]);

    $this->assertFalse($command->option);
});
