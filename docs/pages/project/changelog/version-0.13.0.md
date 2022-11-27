# Changelog 0.13.0 — 31st of July 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.13.0

## Notable changes

**Reworking of messages body and parameters features**

The `\CuyZ\Valinor\Mapper\Tree\Message\Message` interface is no longer a
`Stringable`, however it defines a new method `body` that must return the body
of the message, which can contain placeholders that will be replaced by
parameters.

These parameters can now be defined by implementing the interface
`\CuyZ\Valinor\Mapper\Tree\Message\HasParameters`.

This leads to the deprecation of the no longer needed interface
`\CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage` which had a confusing
name.

```php
final class SomeException
    extends DomainException 
    implements ErrorMessage, HasParameters, HasCode
{
    private string $someParameter;

    public function __construct(string $someParameter)
    {
        parent::__construct();

        $this->someParameter = $someParameter;
    }

    public function body() : string
    {
        return 'Some message / {some_parameter} / {source_value}';
    }

    public function parameters(): array
    {
        return [
            'some_parameter' => $this->someParameter,
        ];
    }

    public function code() : string
    {
        // A unique code that can help to identify the error
        return 'some_unique_code';
    }
}
```

**Handle `numeric-string` type**

The new `numeric-string` type can be used in docblocks.

It will accept any string value that is also numeric.

```php
(new MapperBuilder())->mapper()->map('numeric-string', '42'); // ✅
(new MapperBuilder())->mapper()->map('numeric-string', 'foo'); // ❌
```

**Better mapping error message**

The message of the exception will now contain more information, especially the
total number of errors and the source that was given to the mapper. This change
aims to have a better understanding of what is wrong when debugging.

Before:

``Could not map type `array{foo: string, bar: int}` with the given source.``

After:

``Could not map type `array{foo: string, bar: int}`. An error occurred at path 
bar: Value 'some other string' does not match type `int`.``

## ⚠ BREAKING CHANGES

* Rework messages body and parameters features ([ad1207](https://github.com/CuyZ/Valinor/commit/ad1207153ed5158080f14d7400ed2075b4ff478b))

## Features

* Allow to declare parameter for message ([f61eb5](https://github.com/CuyZ/Valinor/commit/f61eb553fa8f5f740b0bc32288d3704f42113fbf))
* Display more information in mapping error message ([9c1e7c](https://github.com/CuyZ/Valinor/commit/9c1e7c928b5ae518d34e50576e90568110662fc6))
* Handle numeric string type ([96a493](https://github.com/CuyZ/Valinor/commit/96a493469ce69b16683cdb8a44cbbd3faa0ab957))
* Make `MessagesFlattener` countable ([2c1c7c](https://github.com/CuyZ/Valinor/commit/2c1c7cf38a905a92b97f3770ddc08ef0a8bdb695))

## Bug Fixes

* Handle native attribute on promoted parameter ([897ca9](https://github.com/CuyZ/Valinor/commit/897ca9b65e12d0e38c5d6d6776a6bf62bccf2fea))

## Other

* Add fixed value for root node path ([0b37b4](https://github.com/CuyZ/Valinor/commit/0b37b48c60a21ef3e01022d566b8812e13547a72))
* Remove types stringable behavior ([b47a1b](https://github.com/CuyZ/Valinor/commit/b47a1bbb5dd523333007a67c6b47256d0b1bff56))
