<?php

use Illuminate\Console\Command;
use Thettler\LaravelConsoleToolkit\Attributes\Argument;
use Thettler\LaravelConsoleToolkit\Attributes\Option;
use Thettler\LaravelConsoleToolkit\Concerns\UsesConsoleToolkit;
use Thettler\LaravelConsoleToolkit\Tests\Fixtures\Enums\Enum;
use Thettler\LaravelConsoleToolkit\Tests\Fixtures\Enums\IntEnum;
use Thettler\LaravelConsoleToolkit\Tests\Fixtures\Enums\StringEnum;

it('can cast enums to arguments', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public Enum $enumArgument;

        #[Argument]
        public StringEnum $enumStringArgument;

        #[Argument]
        public IntEnum $enumIntArgument;

        #[Argument]
        public StringEnum $enumDefaultArgument = StringEnum::B;

        public function handle()
        {
        }
    };

    $command = $this->callCommand($command, [
        'enumArgument' => 'B',
        'enumStringArgument' => 'String B',
        'enumIntArgument' => 2,
    ]);

    $this->assertSame(Enum::B, $command->enumArgument);

    $this->assertSame(StringEnum::B, $command->enumStringArgument);

    $this->assertSame(IntEnum::B, $command->enumIntArgument);

    $this->assertSame(StringEnum::B, $command->enumDefaultArgument);
});

it('can cast enums to options', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Option]
        public Enum $enumOption;

        #[Option]
        public StringEnum $enumStringOption;

        #[Option]
        public IntEnum $enumIntOption;

        #[Option]
        public StringEnum $enumDefaultOption = StringEnum::B;

        public function handle()
        {
        }
    };

    $command = $this->callCommand($command, [
        '--enumOption' => 'B',
        '--enumStringOption' => 'String B',
        '--enumIntOption' => 2,
    ]);

    $this->assertSame(Enum::B, $command->enumOption);

    $this->assertSame(StringEnum::B, $command->enumStringOption);

    $this->assertSame(IntEnum::B, $command->enumIntOption);

    $this->assertSame(StringEnum::B, $command->enumDefaultOption);
});
