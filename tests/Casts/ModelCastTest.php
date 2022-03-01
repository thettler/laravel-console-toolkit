<?php

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Thettler\LaravelConsoleToolkit\Attributes\Argument;
use Thettler\LaravelConsoleToolkit\Attributes\Option;
use Thettler\LaravelConsoleToolkit\Casts\ModelCaster;
use Thettler\LaravelConsoleToolkit\Concerns\UsesConsoleToolkit;
use Thettler\LaravelConsoleToolkit\Tests\Fixtures\Band;
use Thettler\LaravelConsoleToolkit\Tests\Fixtures\Genre;

beforeEach(function () {
    $db = new DB();

    $db->addConnection([
        'driver' => 'sqlite',
        'database' => ':memory:',
    ]);

    $db->bootEloquent();
    $db->setAsGlobal();

    $this->schema()->create('bands', function (Blueprint $table) {
        $table->id('id');
        $table->string('name');
        $table->text('description');
        $table->foreignIdFor(Genre::class);
        $table->timestamps();
    });

    $this->schema()->create('genres', function (Blueprint $table) {
        $table->id('id');
        $table->string('name');
        $table->timestamps();
    });

    $metal = Genre::create([
        'name' => 'Metal',
    ]);

    $punk = Genre::create([
        'name' => 'Punk Rap',
    ]);

    Band::create([
        'name' => 'Consvmer',
        'description' => 'Fucking nasty breakdowns and low growls',
        'genre_id' => $metal->id,
    ]);

    Band::create([
        'name' => 'KAFVKA',
        'description' => 'FCK AFD. Alle hassen Nazis!',
        'genre_id' => $punk->id,
    ]);
});

afterEach(function () {
    $this->schema()->drop('bands');
});

it('can cast eloquent models', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public Band $band;

        #[Option]
        public Band $optionBand;

        public function handle()
        {
        }
    };

    $command = $this->callCommand($command, [
        'band' => '1',
        '--optionBand' => '2',
    ]);

    expect($command->band)
        ->toBeInstanceOf(Band::class)
        ->toHavekey('id', 1)
        ->toHavekey('name', 'Consvmer')
        ->toHavekey('description', 'Fucking nasty breakdowns and low growls');

    expect($command->optionBand)
        ->toBeInstanceOf(Band::class)
        ->toHavekey('id', 2)
        ->toHavekey('name', 'KAFVKA')
        ->toHavekey('description', 'FCK AFD. Alle hassen Nazis!');
});

it('can cast eloquent models with default', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument]
        public Band $band;

        #[Option]
        public Band $optionBand;

        public function handle()
        {
        }

        public function configureDefaults()
        {
            $this->band = Band::find(2);
            $this->optionBand = Band::find(1);
        }
    };

    $command = $this->callCommand($command);

    expect($command->optionBand)
        ->toBeInstanceOf(Band::class)
        ->toHavekey('id', 1)
        ->toHavekey('name', 'Consvmer');

    expect($command->band)
        ->toBeInstanceOf(Band::class)
        ->toHavekey('id', 2)
        ->toHavekey('name', 'KAFVKA');
});

it('can cast with custom column', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument(
            cast: new ModelCaster(
                findBy: 'name'
            )
        )]
        public Band $band;

        public function handle()
        {
        }
    };

    $command = $this->callCommand($command, [
        'band' => 'KAFVKA',
    ]);

    expect($command->band)
        ->toBeInstanceOf(Band::class)
        ->toHavekey('id', 2)
        ->toHavekey('name', 'KAFVKA');
});

it('can cast with custom column with default', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument(
            cast: new ModelCaster(
                findBy: 'name'
            )
        )]
        public Band $band;

        public function handle()
        {
        }

        public function configureDefaults(): void
        {
            $this->band = Band::find(2);
        }
    };

    $command = $this->callCommand($command);

    expect($command->band)
        ->toBeInstanceOf(Band::class)
        ->toHavekey('id', 2)
        ->toHavekey('name', 'KAFVKA');
});

it('can cast with specific column selects', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument(
            cast: new ModelCaster(
                select: ['id', 'name']
            )
        )]
        public Band $band;

        public function handle()
        {
        }

        public function configureDefaults(): void
        {
            $this->band = Band::find(2);
        }
    };

    $command = $this->callCommand($command);

    expect($command->band)
        ->toBeInstanceOf(Band::class)
        ->toHaveKeys(['name', 'id'])
        ->not
        ->toHaveKeys(['description']);
});

it('can cast with relations', function () {
    $command = new class () extends Command {
        use UsesConsoleToolkit;

        protected $name = 'test';

        #[Argument(
            cast: new ModelCaster(
                with: ['genre']
            )
        )]
        public Band $band;

        public function handle()
        {
        }

        public function configureDefaults(): void
        {
            $this->band = Band::find(2);
        }
    };

    $command = $this->callCommand($command);

    expect($command->band)
        ->toBeInstanceOf(Band::class)
        ->genre
        ->toBeInstanceOf(Genre::class);
});
