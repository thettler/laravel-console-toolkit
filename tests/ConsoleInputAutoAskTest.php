<?php

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Thettler\LaravelConsoleToolkit\Attributes\Argument;
use Thettler\LaravelConsoleToolkit\Attributes\Option;
use Thettler\LaravelConsoleToolkit\Concerns\UsesConsoleToolkit;
use Thettler\LaravelConsoleToolkit\Tests\Fixtures\Enums\Enum;
use Thettler\LaravelConsoleToolkit\Tests\Fixtures\Enums\StringEnum;

it('can ask automatically if value is missing', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'ask:me';

        #[Argument]
        public string $band;

        #[Option]
        public string $emotion;

        public function handle()
        {
            $this->line($this->band.' '.$this->emotion);
        }
    };

    Artisan::starting(fn (Artisan $artisan) => $artisan->add($command));

    \Pest\Laravel\artisan('ask:me --emotion')
        ->expectsQuestion('Please enter "band"', 'Swiss')
        ->expectsQuestion('Please enter "emotion"', 'hyped')
        ->expectsOutput('Swiss hyped')
        ->assertSuccessful();
});

it('only asks for options if there is an required value', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'ask:me';

        #[Option]
        public ?string $band;

        #[Option]
        public string $requiredButNotSpecified;

        #[Option]
        public string $emotion = '';

        #[Option]
        public bool $flag;

        public function handle()
        {
            $this->line('Some Text');
        }
    };

    Artisan::starting(fn (Artisan $artisan) => $artisan->add($command));

    \Pest\Laravel\artisan('ask:me')
        ->expectsOutput('Some Text')
        ->assertSuccessful();
});

it('can give choices if missing input', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'validate';

        #[Argument]
        public Enum $A;

        #[Option]
        public StringEnum $O;

        public function handle()
        {
            $this->line($this->A->name.' '.$this->O->value);
        }
    };

    Artisan::starting(fn (Artisan $artisan) => $artisan->add($command));

    \Pest\Laravel\artisan('validate --O')
        ->expectsChoice('Please enter "A"', Enum::A->name, array_map(fn (object $enum) => $enum->name, Enum::cases()))
        ->expectsChoice(
            'Please enter "O"',
            StringEnum::A->value,
            array_map(fn (object $enum) => $enum->value, StringEnum::cases())
        )
        ->expectsOutput('A String A')
        ->assertSuccessful();
});

it('can ask if validation fails', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'validate';

        #[Argument(
            validation: 'max:5'
        )]
        public string $shortArgument;

        #[Option(
            validation: 'max:5'
        )]
        public string $shortOption;

        public function handle()
        {
            $this->line($this->shortArgument.' '.$this->shortOption);
        }
    };

    Artisan::starting(fn (Artisan $artisan) => $artisan->add($command));

    \Pest\Laravel\artisan('validate LongerThan5 --shortOption=alsoLonger')
        ->expectsOutput('The short argument must not be greater than 5 characters.')
        ->expectsQuestion('Please enter "shortArgument"', 'short')
        ->expectsOutput('The short option must not be greater than 5 characters.')
        ->expectsQuestion('Please enter "shortOption"', 'small')
        ->expectsOutput('short small')
        ->assertSuccessful();
});

it('can disable auto ask on property level', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'validate';

        #[Argument(autoAsk: false)]
        public string $shortArgument;

        #[Option(autoAsk: false)]
        public string $shortOption;

        public function handle()
        {
            $this->line($this->shortArgument.' '.$this->shortOption);
        }
    };

    Artisan::starting(fn (Artisan $artisan) => $artisan->add($command));

    \Pest\Laravel\artisan('validate')
        ->assertSuccessful();
})->expectException(\Symfony\Component\Console\Exception\RuntimeException::class);


it('can not ask array types (yet)', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'validate';

        #[Argument]
        public array $shortArgument;

        #[Option]
        public array $shortOption;

        public function handle()
        {
            $this->line(implode(',', $this->shortArgument).' nothing '.implode(',', $this->shortOption));
        }
    };

    Artisan::starting(fn (Artisan $artisan) => $artisan->add($command));

    \Pest\Laravel\artisan('validate')
        ->expectsOutput(' nothing ')
        ->assertSuccessful();
});
