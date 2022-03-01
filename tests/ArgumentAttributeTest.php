<?php

use Illuminate\Console\Command;
use Thettler\LaravelConsoleToolkit\Attributes\Argument;
use Thettler\LaravelConsoleToolkit\Concerns\UsesConsoleToolkit;
use Thettler\LaravelConsoleToolkit\ConsoleToolkit;

it('Arguments Will Be Registered With Attribute Syntax', function () {
    ConsoleToolkit::enableAutoAsk(false);

    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public string $requiredArgument;

        #[Argument]
        public ?string $optionalArgument;

        #[Argument]
        public string $defaultArgument = 'default_value';

        public function handle()
        {
        }
    };

    $definition = $command->getDefinition();

    $command = $this->callCommand($command, [
        'requiredArgument' => 'Argument_Required',
        'optionalArgument' => 'Argument_Optional',
        'defaultArgument' => 'Argument_Default',
    ]);

    $this->assertTrue($definition->getArgument('requiredArgument')->isRequired());
    $this->assertSame('Argument_Required', $command->requiredArgument);

    $this->assertFalse($definition->getArgument('optionalArgument')->isRequired());
    $this->assertSame('Argument_Optional', $command->optionalArgument);

    $this->assertSame('default_value', $definition->getArgument('defaultArgument')->getDefault());
    $this->assertSame('Argument_Default', $command->defaultArgument);
});

it('Array Arguments WillBe Registered With Attribute Syntax', function () {
    ConsoleToolkit::enableAutoAsk(false);

    $commandRequired = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public array $arrayArgument;

        public function handle()
        {
        }
    };

    $commandOptional = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public ?array $optionalArrayArgument;

        public function handle()
        {
        }
    };

    $commandDefault = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public array $defaultArrayArgument = ['Value A', 'Value B'];

        public function handle()
        {
        }
    };

    $commandRequired = $this->callCommand($commandRequired, [
        'arrayArgument' => ['Array_Required'],
    ]);

    $definition = $commandRequired->getDefinition();

    $this->assertTrue($definition->getArgument('arrayArgument')->isArray());
    $this->assertTrue($definition->getArgument('arrayArgument')->isRequired());
    $this->assertSame(['Array_Required'], $commandRequired->arrayArgument);

    $commandOptional = $this->callCommand($commandOptional, [
        'optionalArrayArgument' => ['Array_Optional'],
    ]);

    $definition = $commandOptional->getDefinition();

    $this->assertTrue($definition->getArgument('optionalArrayArgument')->isArray());
    $this->assertFalse($definition->getArgument('optionalArrayArgument')->isRequired());
    $this->assertSame(['Array_Optional'], $commandOptional->optionalArrayArgument);

    $commandDefault = $this->callCommand($commandDefault, [
        'defaultArrayArgument' => ['Array_Default'],
    ]);

    $definition = $commandDefault->getDefinition();

    $this->assertTrue($definition->getArgument('defaultArrayArgument')->isArray());
    $this->assertFalse($definition->getArgument('defaultArrayArgument')->isRequired());
    $this->assertSame(['Value A', 'Value B'], $definition->getArgument('defaultArrayArgument')->getDefault());
    $this->assertSame(['Array_Default'], $commandDefault->defaultArrayArgument);

    $commandDefault = $this->callCommand($commandDefault, []);
    $this->assertSame(['Value A', 'Value B'], $commandDefault->defaultArrayArgument);
});

it('can describe argument input', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument(
            description: 'Argument Description',
            as: 'argumentAlias',
        )]
        public string $argument = '';

        public function handle()
        {
        }
    };

    $definition = $command->getDefinition();

    $this->assertSame('Argument Description', $definition->getArgument('argumentAlias')->getDescription());

    $command = $this->callCommand($command, [
        'argumentAlias' => 'Value',
    ]);

    $this->assertSame('Value', $command->argument);
});
