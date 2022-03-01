<?php

use Illuminate\Console\Command;
use Thettler\LaravelConsoleToolkit\Attributes\Argument;
use Thettler\LaravelConsoleToolkit\Concerns\UsesConsoleToolkit;
use Thettler\LaravelConsoleToolkit\Exceptions\InvalidTypeException;

it('Inputs need an type', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public $requiredArgument;

        public function handle()
        {
        }
    };

    $this->callCommand($command, [
        'requiredArgument' => 'Argument_Required',
    ]);
})->throws(InvalidTypeException::class, 'A type is required for the console input "requiredArgument".');

it('Inputs only allows named types an type', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public string|int $requiredArgument;

        public function handle()
        {
        }
    };

    $this->callCommand($command, [
        'requiredArgument' => 'Argument_Required',
    ]);
})->throws(InvalidTypeException::class, 'Only named types can be used for the console input "requiredArgument".');
