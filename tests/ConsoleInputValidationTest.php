<?php

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Thettler\LaravelConsoleToolkit\Attributes\Argument;
use Thettler\LaravelConsoleToolkit\Attributes\Option;
use Thettler\LaravelConsoleToolkit\Concerns\UsesConsoleToolkit;
use Thettler\LaravelConsoleToolkit\ConsoleToolkit;
use Thettler\LaravelConsoleToolkit\Tests\Fixtures\Enums\Enum;
use Thettler\LaravelConsoleToolkit\Tests\Fixtures\Enums\StringEnum;
use Thettler\LaravelConsoleToolkit\Transfers\Validation;

it('can add validation to console inputs', function () {
    ConsoleToolkit::enableAutoAsk(false);

    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'validate';

        #[Argument(
            validation: 'max:5'
        )]
        public string $shortArgument;

        #[Option(
            validation: ['max:5']
        )]
        public string $shortOption;

        public function handle()
        {
            $this->line($this->shortArgument . ' ' . $this->shortOption);
        }
    };

    Artisan::starting(fn (Artisan $artisan) => $artisan->add($command));

    \Pest\Laravel\artisan('validate LongerThan5 --shortOption=alsoLonger')
        ->expectsOutput('The short argument must not be greater than 5 characters.')
        ->expectsOutput('The short option must not be greater than 5 characters.')
        ->doesntExpectOutput('LongerThan5 alsoLonger')
        ->assertFailed();
});

it('can sets automatic enum rules', function () {
    ConsoleToolkit::enableAutoAsk(false);

    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'validate';

        #[Argument]
        public Enum $A;

        #[Option]
        public StringEnum $O;

        public function handle()
        {
            $this->line($this->A->name . ' ' . $this->O->value);
        }
    };

    Artisan::starting(fn (Artisan $artisan) => $artisan->add($command));

    \Pest\Laravel\artisan('validate notValid --O=notValid')
        ->expectsOutput('The selected a is invalid.')
        ->expectsOutput('Possible values for: A.')
        ->expectsOutput('   - ' . Enum::A->name)
        ->expectsOutput('   - ' . Enum::B->name)
        ->expectsOutput('   - ' . Enum::C->name)
        ->expectsOutput('The selected o is invalid.')
        ->expectsOutput('Possible values for: O.')
        ->expectsOutput('   - ' . StringEnum::A->value)
        ->expectsOutput('   - ' . StringEnum::B->value)
        ->expectsOutput('   - ' . StringEnum::C->value)
        ->assertFailed();
});

it('can add custom validation messages', function () {
    ConsoleToolkit::enableAutoAsk(false);

    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'validate';

        #[Argument(
            validation: new Validation(
                rules: ['max:5'],
                messages: ['max' => 'This argument :attribute is to long for you boy.']
            )
        )]
        public string $shortArgument;

        #[Option(
            validation: new Validation(
                rules: 'max:5',
                messages: ['max' => 'This option :attribute is to long for you boy.']
            )
        )]
        public string $shortOption;

        public function handle()
        {
            $this->line($this->shortArgument . ' ' . $this->shortOption);
        }
    };

    Artisan::starting(fn (Artisan $artisan) => $artisan->add($command));

    \Pest\Laravel\artisan('validate LongerThan5 --shortOption=alsoLonger')
        ->expectsOutput('This argument short argument is to long for you boy.')
        ->expectsOutput('This option short option is to long for you boy.')
        ->doesntExpectOutput('LongerThan5 alsoLonger')
        ->assertFailed();
});
