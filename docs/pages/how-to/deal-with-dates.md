# Dealing with dates

When the mapper builds a date object, it has to know which format(s) are
supported. By default, any valid timestamp or RFC 3339-formatted value will be
accepted.

If other formats are to be supported, they need to be registered using the
following method:

```php
(new \CuyZ\Valinor\MapperBuilder())
    // Both `Cookie` and `ATOM` formats will be accepted
    ->supportDateFormats(DATE_COOKIE, DATE_ATOM)
    ->mapper()
    ->map(DateTimeInterface::class, 'Monday, 08-Nov-1971 13:37:42 UTC');
```

## Custom date class implementation

By default, the library will map a `DateTimeInterface` to a `DateTimeImmutable`
instance. If other implementations are to be supported, custom constructors can
be used.

Here is an implementation example for the [nesbot/carbon] library:

```php
(new MapperBuilder())
    // When the mapper meets a `DateTimeInterface` it will convert it to Carbon
    ->infer(DateTimeInterface::class, fn () => \Carbon\Carbon::class)
    
    // We teach the mapper how to create a Carbon instance
    ->registerConstructor(function (string $time): \Carbon\Carbon {
        // Only `Cookie` format will be accepted
        return Carbon::createFromFormat(DATE_COOKIE, $time);
    })
    
    // Carbon uses its own exceptions, so we need to wrap it for the mapper
    ->filterExceptions(function (Throwable $exception) {
        if ($exception instanceof \Carbon\Exceptions\Exception) {
            return \CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder::from($exception);
        }
                    
        throw $exception;
    })
    
    ->mapper()
    ->map(DateTimeInterface::class, 'Monday, 08-Nov-1971 13:37:42 UTC');
```

[nesbot/carbon]: https://github.com/briannesbitt/Carbon
