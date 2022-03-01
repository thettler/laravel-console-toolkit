<?php

namespace Thettler\LaravelConsoleToolkit\Tests\Fixtures;

use Illuminate\Console\Command;
use Thettler\LaravelConsoleToolkit\Attributes\ArtisanCommand;
use Thettler\LaravelConsoleToolkit\Concerns\UsesConsoleToolkit;

#[ArtisanCommand(
    name: 'test:basic',
    description: 'Basic Command description!',
    help: 'Some Help.',
    hidden: true,
    aliases: ['alias:basic']
)]
class AttributeCommand extends Command
{
    use UsesConsoleToolkit;

    public function handle()
    {
    }
}
