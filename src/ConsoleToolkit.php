<?php

namespace Thettler\LaravelConsoleToolkit;

use Thettler\LaravelConsoleToolkit\Contracts\Caster;

/**
 * @phpstan-type CasterConfigKey class-string<Caster>
 * @phpstan-type CasterConfigValue class-string | callable(mixed, \ReflectionProperty): bool | array<class-string>
 */
class ConsoleToolkit
{
    /** @var array<CasterConfigKey, CasterConfigValue> */
    public static array $casts = [];

    public static bool $hasAutoAskEnabled = false;

    /**
     * @param  CasterConfigKey  $caster
     * @param  CasterConfigValue $matches
     * @return void
     */
    public static function addCast(string $caster, array|string|callable $matches): void
    {
        static::$casts[$caster] = $matches;
    }

    /**
     * @param array<CasterConfigKey, CasterConfigValue>  $caster
     * @return void
     */
    public static function setCast(array $caster): void
    {
        static::$casts = $caster;
    }

    public static function enableAutoAsk(bool $enable = true): void
    {
        static::$hasAutoAskEnabled = $enable;
    }
}
