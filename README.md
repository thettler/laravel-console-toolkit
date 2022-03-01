# Laravel Console Toolkit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/thettler/laravel-console-toolkit.svg?style=flat-square)](https://packagist.org/packages/thettler/laravel-console-toolkit)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/thettler/laravel-console-toolkit/run-tests?label=tests)](https://github.com/thettler/laravel-console-toolkit/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/thettler/laravel-console-toolkit/Check%20&%20fix%20styling?label=code%20style)](https://github.com/thettler/laravel-console-toolkit/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/thettler/laravel-console-toolkit.svg?style=flat-square)](https://packagist.org/packages/thettler/laravel-console-toolkit)
[![PHP Version](https://img.shields.io/packagist/php-v/thettler/laravel-console-toolkit?style=flat-square)](https://packagist.org/packages/thettler/laravel-console-toolkit)


![Header Image](/.github/header_img.png)

This package makes it even easier to write maintainable and expressive Artisan commands, with argument/option casting,
validation and autoAsk. Also, it lets you define your arguments/options with simple properties and attributes for better
ide support and static analysis. And all this with a single trait.

## 🤯 Features

All the features:

| Support | Name                  | Description                                                                                                              |
|:-------:|:----------------------|--------------------------------------------------------------------------------------------------------------------------|
|    ✅    | Laravel Features      | Supports everything laravel can do                                                                                       |
|    ✅    | Attribute Syntax      | Use PHP-Attributes to automatically define your inputs based on types                                                    |
|    ✅    | Casting               | Automatically cast your inputs to Enums, Models, Objects or anything you want                                            |
|    ✅    | Validation            | Use the Laravel Validator to validate the inputs from the console                                                        |
|    ✅    | Auto Ask              | If the user provides an invalid value toolkit will ask again for a valid value without the need to run the command again |
|    ✅    | Negatable Options     | Options can be specified as opposites: --dry or --no-dry                                                                 |
|    ✅    | Option required Value | Options can have required values                                                                                         |

## :purple_heart:  Support me

Visit my blog on [https://bitbench.dev](https://bitbench.dev) or follow me on Social Media
[Twitter @bitbench](https://twitter.com/bitbench)
[Instagram @bitbench.dev](https://www.instagram.com/bitbench.dev/)

## :package:  Installation

You can install the package via composer:

```bash
composer require thettler/laravel-console-toolkit
```

## :wrench:  Usage

> :right_anger_bubble:  Before you use this package you should already have an understanding of Artisan Commands. You can read about them [here](https://laravel.com/docs/8.x/artisan).

### A Basic Command

To use the Toolkit you simply need to add the `UsesConsoleToolkit` trait inside your command.

Then add the `Thettler\LaravelConsoleToolkit\Attributes\ArtisanCommand` to the class to specify the name and other
things like description, help, and so on.

The `ArtisanCommand` requires the `name` parameter to be set. This will be the name of the Command which you can use to
call it from the commandline.

```php
<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Thettler\LaravelConsoleToolkit\Concerns\UsesConsoleToolkit;

#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;
    
    public function handle()
    {
    }
}
```

And call it like:

```bash
php artisan basic
```

<details><summary>Traditional Syntax</summary>
<p>

```php
    
<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;

class BasicCommand extends Command
{
    protected $signature = 'basic';

    public function handle()
    {
    }
}
```

</p>
</details>

### Descriptions, Help and Hidden Commands

If you want to add a description, a help comment or mark the command as hidden, you can specify this on
the `ArtisanCommand` Attribute like this:

```php
#[ArtisanCommand(
    name: 'basic',
    description: 'Some useful description.',
    help: 'Some helpful text.',
    hidden: true
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    ...
}
```

> I like to use named arguments for a more readable look.

<details><summary>Traditional Syntax</summary>
<p>

```php
...
class BasicCommand extends Command
{
    protected $signature = 'basic';

    protected $description = 'Some useful description.';

    protected $help = 'Some helpful text.';
    
    protected $hidden = true;
    ...
}
```

</p>
</details>

### Defining Input Expectations

The basic workflow to add an argument or option is always to add a property and decorate it with an Attribute.
`#[Option]` if you want an option and `#[Argument]` if you want an argument. The property will be hydrated with the
value from the command line, so you can use it like any normal property inside your `handle()` method.

More about that in the following sections. :arrow_down:

> :exclamation: The property will only be hydrated inside the `handle()` method. Keep that in mind.

### Arguments

To define Arguments you create a property and add the `Argument` attribute to it. The property will be hydrated with the
value from the command line, so you can use it like any normal property inside your `handle()` method.

```php
...
use \Thettler\LaravelConsoleToolkit\Attributes\Argument;

#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Argument]
    protected string $myArgument;
    
    public function handle() {
        $this->line($this->myArgument);
    }
}
```

call it like:

```bash
php artisan basic myValue
# Output:
# myValue
```

<details><summary>Traditional Syntax</summary>
<p>

```php
class BasicCommand extends Command
{
    protected $signature = 'basic {myArgument}';
    
    public function handle() {
        $this->line($this->argument('myArgument'));
    }
}
```

</p>
</details>

#### Array Arguments

You can also use arrays in arguments, simply typehint the property as `array`.

```php
#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Argument]
    protected array $myArray;
    
    public function handle() {
        $this->line(implode(', ', $this->myArray));
    }
}
```

Call it like:

```bash
php artisan basic Item1 Item2 Item3 
# Output
# Item1, Item2, Item3 
```

<details><summary>Traditional Syntax</summary>
<p>

```php
class BasicCommand extends Command
{
    protected $signature = 'basic {myArgument*}';
    
    public function handle() {
        $this->line($this->argument('myArgument'));
    }
}
```

</p>
</details>

#### Optional Arguments

Of course, you can use optional arguments as well. To achieve this you simply make the property nullable.

> :information_source: This works with `array` as well but the property won't be null but an empty array
> instead

```php
#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Argument]
    protected ?string $myArgument;
    
    ...
}
```

<details><summary>Traditional Syntax</summary>
<p>

```php
class BasicCommand extends Command
{
    protected $signature = 'basic {myArgument?}';
    
    ...
}
```

</p>
</details>

If your argument should have a default value, you can assign a value to the property which will be used as default
value.

```php
#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Argument]
    protected string $myArgument = 'default';
    
    ...
}
```

<details><summary>Traditional Syntax</summary>
<p>

```php
class BasicCommand extends Command
{
    protected $signature = 'basic {myArgument=default}';
    
    ...
}
```

</p>
</details>

#### Argument Description

You can set a description for arguments as parameter on the `Argument` Attribute.

```php
#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Argument(
        description: 'Argument Description'
    )]
    protected string $myArgument;
    
    ...
}
```

<details><summary>Traditional Syntax</summary>
<p>

```php
...
class BasicCommand extends Command
{
    protected $signature = 'basic {myArgument: Argument Description}';
    
    ...
}
```

</p>
</details>

> :exclamation: :exclamation: If you have more than one argument the order inside the class will also be the order on the commandline

### Options

To use options in your commands you use the `Options` Attribute. If you have set a typehint of `boolean` it will be
false if the option was not set and true if it was set.

```php
use \Thettler\LaravelConsoleToolkit\Attributes\Option;

#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Option]
    protected bool $myOption;
    
    public function handle() {
        dump($this->myOption);
    }
}
```

Call it like:

```bash
php artisan basic --myOption
# Output
# true
```

```bash
php artisan basic
# Output
# false
```

<details><summary>Traditional Syntax</summary>
<p>

```php
class BasicCommand extends Command
{
    protected $signature = 'basic {--myOption}';
    
    public function handle() {
        dump($this->option('myOption'));
    }
}
```

</p>
</details>

#### Value Options

You can add a value to an option if you type hint the property with something different as `bool`. This will
automatically make it to an option with a value. If your typehint is not nullable the option will have a required value.
This means the option can only be used with a value.

:x: Wont work `--myoption` :white_check_mark: works `--myoption=myvalue`

If you want to make the value optional simply make the type nullable or assign a value to the property

```php
#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Option]
    protected string $requiredValue; // if the option is used the User must specify a value  
    
    #[Option]
    protected ?string $optionalValue; // The value is optional

    #[Option]
    protected string $defaultValue = 'default'; // The option has a default value

    #[Option]
    protected array $array; // an Array Option 

    #[Option]
    protected array $defaultArray = ['default1', 'default2']; // an Array Option with default
    ...
}
```

Call it like:

```bash
php artisan basic --requiredValue=someValue --optionalValue --array=Item1 --array=Item2
```

<details><summary>Traditional Syntax</summary>
<p>

```php
class BasicCommand extends Command
{
    // requiredValue is not possible
    // defaultArray is not possible
    protected $signature = 'basic {--optionalValue=} {--defaultValue=default} {--array=*}';
   
   ...
}
```

</p>
</details>

#### Option Description

You can set a description for an option on the `Option` Attribute.

```php
#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Option(
        description: 'Option Description'
    )]
    protected bool $option;
    ...
}
```

<details><summary>Traditional Syntax</summary>
<p>

```php
class BasicCommand extends Command
{
    protected $signature = 'basic {--option: Option Description}';
}
```

</p>
</details>

#### Option Shortcuts

You can set a shortcut for an option on the `Option` Attribute.

> :warning: Be aware that a shortcut can only be one char long

```php
#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Option(
        shortcut: 'Q'
    )]
    protected bool $option;
    ...
}
```

Call it like:

```bash
php artisan basic -Q
```

<details><summary>Traditional Syntax</summary>
<p>

```php
class BasicCommand extends Command
{
    protected $signature = 'basic {--Q|option}';
}
```

</p>
</details>

#### Negatable Options

You can make option negatable by adding the negatable parameter to the `Option` Attribute. Now the option accepts either
the flag (e.g. --yell) or its negation (e.g. --no-yell).

```php
#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Option(
        negatable: true
    )]
    protected bool $yell;
    
    public function handle(){
       dump($this->yell); // true if called with --yell
       dump($this->yell); // false if called with --no-yell
    }
}
```

Call it like:

```bash
php artisan basic --yell
php artisan basic --no-yell
```

#### Enum Types

It is also possible to type `Arguments` or `Options` as Enum. The Package will automatically cast the input from the
commandline to the typed Enum. If you use BackedEnums you use the value of the case and if you have a non backed Enum
you use the name of the case.

```php
enum Enum
{
    case A;
    case B;
    case C;
}

enum IntEnum: int
{
    case A = 1;
    case B = 2;
    case C = 3;
}

enum StringEnum: string
{
    case A = 'String A';
    case B = 'String B';
    case C = 'String C';
}
```

```php
    #[Argument]
    protected Enum $argEnum;

    #[Argument]
    protected StringEnum $argStringEnum;

    #[Argument]
    protected IntEnum $argIntEnum;

    #[Option]
    protected Enum $enum;

    #[Option]
    protected StringEnum $stringEnum;

    #[Option]
    protected IntEnum $intEnum;
```

```bash
php artisan enum B "String B" 2 --enum=B --stringEnum="String B" --intEnum=2
```

### Input alias

By default, the input name used on the commandline will be same as the property name. You can change this with the `as`
parameter on the `Option` or `Argument` Attribute. This can be handy if you have conflicting property names or want a
more expressive api for your commands.

> :warning: If you use the `->option()` syntax you need to specify the alias name to get the option.

```php
#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Argument(
        as: 'alternativeArgument'
    )]
    protected string $myArgument;
 
    #[Option(
        as: 'alternativeName'
    )]
    protected bool $myOption;
    
    public function handle(){
       dump($this->myArgument);
       dump($this->myOption);
    }
}
```

Call it like:

```bash
php artisan basic something --alternativeName
```

### Special Default values

If you want to use some objects with casts as default values you can use the `configureDefauls()` method on the command
to set default values.

```php
#[ArtisanCommand(
    name: 'basic',
)]
class BasicCommand extends Command
{
    use UsesConsoleToolkit;

    #[Argument]
    protected BandModel $band;

    public function configureDefaults(): void {
        $this->band = BandModel::find('2');
    }
    
    public function handle(){
       dump($this->band); // The Band with id 2
    }
}
```

### Casts

Cast can be specified on `Arguments` and `Options`. You can either provide a class-string of a caster to use or an
instance of the caster. This is helpful to configure the caster via the constructor.

#### Model Cast

The Toolkit provides a cast for eloquent models out of the box. So if you typehint an eloquent model toolkit will try to
match the console input to the primary key of the model and fetches it from the database.

```php
    #[Argument]
    protected BandModel $band;
    
    public function handle(){
        $this->band // Well be an instance of BandModel 
    }
```

If you want to change the column that will be used to match the input to the database, load relations or only select
specific columns you can use the manual cast like this:

```php
    #[Argument(
        cast: new \Thettler\LaravelConsoleToolkit\Casts\ModelCaster(
            findBy: 'name',
            select: ['id', 'name']
            with: ['songs']
        )
    )]
    protected BandModel $band;
    
    public function handle(){
        $this->band // Will be an instance of BandModel 
    }
```

#### Enum Cast

The enum cast will automatically cast every typed enum to this enum. But you can also manually specify it like so.

```php
    #[Argument(
        cast: \Thettler\LaravelConsoleToolkit\Casts\EnumCaster::class
    )]
    protected Enum $argEnum;

    #[Option(
        cast: new \Thettler\LaravelConsoleToolkit\Casts\EnumCaster(Enum::class)
    )]
    protected Enum $enum;
```

#### Array Cast

If you have an array and want to cast all its values to a specific type you can use the ArrayCaster. It expects a caster
and a specific type:

```php
    #[Argument(
        cast: new \Thettler\LaravelConsoleToolkit\Casts\ArrayCaster(
            caster: \Thettler\LaravelConsoleToolkit\Casts\EnumCaster::class, 
            type: StringEnum::class
        )
    )]
    protected array $enumArray;

    #[Option(
        cast: new \Thettler\LaravelConsoleToolkit\Casts\ArrayCaster(
            caster: \Thettler\LaravelConsoleToolkit\Casts\EnumCaster::class, 
            type: StringEnum::class
        )
    )]
    protected array $enumArray2;
```

#### Custom Casts

It's also possible to define your own casts. To do so you need to create a class that implements the `Caster` Interface.

Let's have a look at small UserCast that allows to simply use the id of a user model on the command line and
automatically fetch the correct user from the database:

```php
<?php

class UserCast implements Caster
{
    /**
    * This method deals with the conversion from the default value to a value the console understand so only 
    * basic return types are allowed  
    * 
    * @param mixed $value The default value if one is present
    * @param string $type The type is a string representation of the type of the property 
    * @param \ReflectionProperty $property The property reflection itself for more control
    * @return int|float|array|string|bool|null
     */
    public function from(mixed $value, string $type, \ReflectionProperty $property): int|float|array|string|bool|null
    {
        if ($value instanceof Band){
            return $value->getKey();
        }
        
        throw new Exception(self::class . ' can only be used with type '. Band::class)
    }

    /**
     * This method deals with the conversion from console input to property value 
     * 
     * @param  mixed  $value The Value from the command line
     * @param  class-string<Band>  $type The type is a string representation of the type of the property 
     * @param  \ReflectionProperty  $property The property reflection itself for more control
     * @return mixed
     */
    public function to(mixed $value, string $type, \ReflectionProperty $property)
    {
        return $type::find($value);
    }
}
```

Now you can use this cast ether locally on an attribute or register it globally for automatic casting like 
this in your AppServiceProvider

```php
    /** Uses the UserCaster everytime the User class is typehint on an Argument or Option */
    \Thettler\LaravelConsoleToolkit\ConsoleToolkit::addCast(UserCaster::class, User::class);

    /** Uses the UserCaster everytime the User or MasterUser class is typehint on an Argument or Option */
    \Thettler\LaravelConsoleToolkit\ConsoleToolkit::addCast(UserCaster::class, [User::class, MasterUser::class]);

    /** Uses the UserCaster everytime the callable returns true */
    \Thettler\LaravelConsoleToolkit\ConsoleToolkit::addCast(
        UserCaster::class,
        fn (mixed $value, ReflectionProperty $property): bool  =>  is_subclass_of($property->getType()->getName(), User::class);
    );
```

### Validation
You can also use the normal laravel validation rules to validate the input.
```php
    #[Argument(
        validation: ['max:5']
    )]
    protected string $validated;
```

If you want custom messages you need to use the Validation object
```php
    #[Argument(
        validation: new \Thettler\LaravelConsoleToolkit\Transfers\Validation(
            rules: ['max:5']
            messages: [
                'max' => 'This is way to much!'
            ]   
        )
    )]
    protected string $validated;
```

### Auto Ask 
By default, Auto Ask is enabled. Every time a command is called with an input that fails validation or is required but not
specified the command automatically asks the user to enter a (new) value. If the type is an enum it will give the user
choice with all the enum values. 

If you want to disable this behavior you can do it locally:

```php
    #[Argument(
        autoAsk: false
    )]
    protected string $dontAsk;
```
or globally in your AppServiceProvider:

```php
    \Thettler\LaravelConsoleToolkit\ConsoleToolkit::enableAutoAsk(false);
```

## :robot:  Testing

```bash
composer test
```

## :open_book:  Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## :angel:  Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## :lock:  Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## :copyright:  Credits

- [Tobias Hettler](https://github.com/thettler)
- [All Contributors](../../contributors)

## :books:  License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
