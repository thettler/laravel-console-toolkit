<?php

namespace Thettler\LaravelConsoleToolkit\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ArtisanCommand
{
    public function __construct(
        public readonly string $name,
        public readonly string $description = '',
        public readonly string $help = '',
        public readonly bool $hidden = false,
        public readonly array $aliases = []
    ) {
    }
}
