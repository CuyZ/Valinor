# Changelog 0.4.0 — 7th of January 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.4.0

## Notable changes

**Allow mapping to any type**

Previously, the method `TreeMapper::map` would allow mapping only to an object.
It is now possible to map to any type handled by the library.

It is for instance possible to map to an array of objects:

```php
$objects = (new MapperBuilder())->mapper()->map(
    'array<' . SomeClass::class . '>',
    [/* … */]
);
```

For simple use-cases, an array shape can be used:

```php
$array = (new MapperBuilder())->mapper()->map(
    'array{foo: string, bar: int}',
    [/* … */]
);

echo $array['foo'];
echo $array['bar'] * 2;
```

This new feature changes the possible behaviour of the mapper, meaning static
analysis tools need help to understand the types correctly. An extension for
PHPStan and a plugin for Psalm are now provided and can be included in a project
to automatically increase the type coverage.

---

**Better handling of messages**

When working with messages, it can sometimes be useful to customize the content
of a message — for instance to translate it.

The helper
class `\CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter` can be
used to provide a list of new formats. It can be instantiated with an array
where each key represents either:

- The code of the message to be replaced
- The content of the message to be replaced
- The class name of the message to be replaced

If none of those is found, the content of the message will stay unchanged unless
a default one is given to the class.

If one of these keys is found, the array entry will be used to replace the
content of the message. This entry can be either a plain text or a callable that
takes the message as a parameter and returns a string; it is for instance
advised to use a callable in cases where a translation service is used — to
avoid useless greedy operations.

In any case, the content can contain placeholders that will automatically be
replaced by, in order:

1. The original code of the message
2. The original content of the message
3. A string representation of the node type
4. The name of the node
5. The path of the node

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(SomeClass::class, [/* … */]);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    $node = $error->node();
    $messages = new \CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener($node);

    $formatter = (new MessageMapFormatter([
        // Will match if the given message has this exact code
        'some_code' => 'new content / previous code was: %1$s',
    
        // Will match if the given message has this exact content
        'Some message content' => 'new content / previous message: %2$s',
    
        // Will match if the given message is an instance of `SomeError`
        SomeError::class => '
            - Original code of the message: %1$s
            - Original content of the message: %2$s
            - Node type: %3$s
            - Node name: %4$s
            - Node path: %5$s
        ',
    
        // A callback can be used to get access to the message instance
        OtherError::class => function (NodeMessage $message): string {
            if ((string)$message->type() === 'string|int') {
                // …
            }
    
            return 'Some message content';
        },
    
        // For greedy operation, it is advised to use a lazy-callback
        'bar' => fn () => $this->translator->translate('foo.bar'),
    ]))
        ->defaultsTo('some default message')
        // …or…
        ->defaultsTo(fn () => $this->translator->translate('default_message'));

    foreach ($messages as $message) {
        echo $formatter->format($message);    
    }
}
```

---

**Automatic union of objects inferring during mapping**

When the mapper needs to map a source to a union of objects, it will try to
guess which object it will map to, based on the needed arguments of the objects,
and the values contained in the source.

```php
final class UnionOfObjects
{
    public readonly SomeFooObject|SomeBarObject $object;
}

final class SomeFooObject
{
    public readonly string $foo;
}

final class SomeBarObject
{
    public readonly string $bar;
}

// Will map to an instance of `SomeFooObject`
(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(UnionOfObjects::class, ['foo' => 'foo']);

// Will map to an instance of `SomeBarObject`
(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(UnionOfObjects::class, ['bar' => 'bar']);
```

## ⚠ BREAKING CHANGES

* Add access to root node when error occurs during mapping ([54f608](https://github.com/CuyZ/Valinor/commit/54f608e5b1a4bbd508246c063a3e79df48c1ddeb))
* Allow mapping to any type ([b2e810](https://github.com/CuyZ/Valinor/commit/b2e810e3ce997181b7cfcc96d2bccb9c56a5bdd8))
* Allow object builder to yield arguments without source ([8a7414](https://github.com/CuyZ/Valinor/commit/8a74147d4c1f78696049c2dfb9acf979ef6e4689))
* Wrap node messages in proper class ([a805ba](https://github.com/CuyZ/Valinor/commit/a805ba0442f9ac8dd6b2499426ffa6fcb190d4be))

## Features

* Introduce automatic union of objects inferring during mapping ([79d7c2](https://github.com/CuyZ/Valinor/commit/79d7c266ecabc069b11120338ae1e62a8f3dca97))
* Introduce helper class `MessageMapFormatter` ([ddf69e](https://github.com/CuyZ/Valinor/commit/ddf69efaaa4eeab87126bdc7f43b92d5faa06f46))
* Introduce helper class `MessagesFlattener` ([a97b40](https://github.com/CuyZ/Valinor/commit/a97b406154fa5bccc2f874634ed24af69401cd4a))
* Introduce helper `NodeTraverser` for recursive operations on nodes ([cc1bc6](https://github.com/CuyZ/Valinor/commit/cc1bc66bbece7f79dfec093e33e96d3e31bca4e9))

## Bug Fixes

* Handle nested attributes compilation ([d2795b](https://github.com/CuyZ/Valinor/commit/d2795bc6b9ee53f9896889cabc1006be2586b008))
* Treat forbidden mixed type as invalid type ([36bd36](https://github.com/CuyZ/Valinor/commit/36bd3638c8e48d2c43d247991b8d2055e8ab56bb))
* Treat union type resolving error as message ([e834cd](https://github.com/CuyZ/Valinor/commit/e834cdc5d3384d4295582f8d602b5da5f656e83a))
* Use locked package versions for quality assurance workflow ([626f13](https://github.com/CuyZ/Valinor/commit/626f135eee5b4954845e954d567babd58f6ca51b))

## Other

* Ignore changelog configuration file in git export ([85a6a4](https://github.com/CuyZ/Valinor/commit/85a6a49ce20b96f2e023a7e824b8085ea198fd39))
* Raise PHPStan version ([0144bf](https://github.com/CuyZ/Valinor/commit/0144bf084a4883ecbdb65155a05b2ea90e7fca61))
