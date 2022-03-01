<?php

namespace Thettler\LaravelConsoleToolkit\Enums;

enum ConsoleInputType: string
{
    case Argument = 'argument';
    case Option = 'option';
}
