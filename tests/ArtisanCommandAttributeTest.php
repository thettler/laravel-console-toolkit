<?php

use Thettler\LaravelConsoleToolkit\Tests\Fixtures\AttributeCommand;

it('can register command with attribute', function () {
    $command = new AttributeCommand();
    $command = $this->callCommand($command);

    $this->assertSame('test:basic', $command->getName());
    $this->assertSame('Basic Command description!', $command->getDescription());
    $this->assertSame('Some Help.', $command->getHelp());
    $this->assertTrue($command->isHidden());
    $this->assertSame(['alias:basic'], $command->getAliases());
});
