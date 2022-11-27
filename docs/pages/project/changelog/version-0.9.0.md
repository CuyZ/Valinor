# Changelog 0.9.0 — 23rd of May 2022

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/0.9.0

## Notable changes

**Cache injection and warmup**

The cache feature has been revisited, to give more control to the user on how
and when to use it.

The method `MapperBuilder::withCacheDir()` has been deprecated in favor of a new
method `MapperBuilder::withCache()` which accepts any PSR-16 compliant
implementation.

> **Warning**
>
> These changes lead up to the default cache not being automatically
> registered anymore. If you still want to enable the cache (which you should),
> you will have to explicitly inject it (see below).

A default implementation is provided out of the box, which saves cache entries
into the file system.

When the application runs in a development environment, the cache implementation
should be decorated with `FileWatchingCache`, which will watch the files of the
application and invalidate cache entries when a PHP file is modified by a
developer — preventing the library not behaving as expected when the signature
of a property or a method changes.


The cache can be warmed up, for instance in a pipeline during the build and
deployment of the application — kudos to @boesing for the feature!

> **Note** The cache has to be registered first, otherwise the warmup will end
> up being useless.

```php
$cache = new \CuyZ\Valinor\Cache\FileSystemCache('path/to/cache-directory');

if ($isApplicationInDevelopmentEnvironment) {
    $cache = new \CuyZ\Valinor\Cache\FileWatchingCache($cache);
}

$mapperBuilder = (new \CuyZ\Valinor\MapperBuilder())->withCache($cache);

// During the build:
$mapperBuilder->warmup(SomeClass::class, SomeOtherClass::class);

// In the application:
$mapperBuilder->mapper()->map(SomeClass::class, [/* … */]);
```

---

**Message formatting & translation**

Major changes have been made to the messages being returned in case of a mapping
error: the actual texts are now more accurate and show better information.

> **Warning** 
> 
> The method `NodeMessage::format` has been removed, message formatters should 
> be used instead. If needed, the old behaviour can be retrieved with the
> formatter `PlaceHolderMessageFormatter`, although it is strongly advised to 
> use the new placeholders feature (see below).
> 
> The signature of the method `MessageFormatter::format` has changed as well.

It is now also easier to format the messages, for instance when they need to be
translated. Placeholders can now be used in a message body, and will be replaced 
with useful information.

| Placeholder          | Description                                          |
|----------------------|:-----------------------------------------------------|
| `{message_code}`     | the code of the message                              |
| `{node_name}`        | name of the node to which the message is bound       |
| `{node_path}`        | path of the node to which the message is bound       |
| `{node_type}`        | type of the node to which the message is bound       |
| `{original_value}`   | the source value that was given to the node          |
| `{original_message}` | the original message before being customized         |

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(SomeClass::class, [/* … */]);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    $node = $error->node();
    $messages = new \CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener($node);

    foreach ($messages as $message) {
        if ($message->code() === 'some_code') {
            $message = $message->withBody('new message / {original_message}');
        }

        echo $message;
    }
}
```

The messages are formatted using the [ICU library], enabling the placeholders to
use advanced syntax to perform proper translations, for instance currency
support.

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())->mapper()->map('int<0, 100>', 1337);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    $message = $error->node()->messages()[0];

    if (is_numeric($message->value())) {
        $message = $message->withBody(
            'Invalid amount {original_value, number, currency}'
        );    
    } 

    // Invalid amount: $1,337.00
    echo $message->withLocale('en_US');
    
    // Invalid amount: £1,337.00
    echo $message->withLocale('en_GB');
    
    // Invalid amount: 1 337,00 €
    echo $message->withLocale('fr_FR');
}
```

See [ICU documentation] for more information on available syntax.

> **Warning** If the `intl` extension is not installed, a shim will be
> available to replace the placeholders, but it won't handle advanced syntax as
> described above.

The formatter `TranslationMessageFormatter` can be used to translate the content
of messages.

The library provides a list of all messages that can be returned; this list can
be filled or modified with custom translations.

```php
\CuyZ\Valinor\Mapper\Tree\Message\Formatter\TranslationMessageFormatter::default()
    // Create/override a single entry…
    ->withTranslation('fr', 'some custom message', 'un message personnalisé')
    // …or several entries.
    ->withTranslations([
        'some custom message' => [
            'en' => 'Some custom message',
            'fr' => 'Un message personnalisé',
            'es' => 'Un mensaje personalizado',
        ], 
        'some other message' => [
            // …
        ], 
    ])
    ->format($message);
```

It is possible to join several formatters into one formatter by using the
`AggregateMessageFormatter`. This instance can then easily be injected in a
service that will handle messages.

The formatters will be called in the same order they are given to the aggregate.

```php
(new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\AggregateMessageFormatter(
    new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\LocaleMessageFormatter('fr'),
    new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter([
        // …
    ],
    \CuyZ\Valinor\Mapper\Tree\Message\Formatter\TranslationMessageFormatter::default(),
))->format($message)
```

[ICU library]: https://unicode-org.github.io/icu/

[ICU documentation]: https://unicode-org.github.io/icu/userguide/format_parse/messages/

## ⚠ BREAKING CHANGES

* Improve message customization with formatters ([60a665](https://github.com/CuyZ/Valinor/commit/60a66561413fc0d366cae9acf57ac553bb1a919d))
* Revoke `ObjectBuilder` API access ([11e126](https://github.com/CuyZ/Valinor/commit/11e12624aad2378465122b987e53e76c744d2179))

## Features

* Allow injecting a cache implementation that is used by the mapper ([69ad3f](https://github.com/CuyZ/Valinor/commit/69ad3f47777f2bec4e565b98035f07696cd16d35))
* Extract file watching feature in own cache implementation ([2d70ef](https://github.com/CuyZ/Valinor/commit/2d70efbfbb73dfe02987de82ee7fc5bb38b3e486))
* Improve mapping error messages ([05cf4a](https://github.com/CuyZ/Valinor/commit/05cf4a4a4dc40af735ba12af5bc986ffec015c6c))
* Introduce method to warm the cache up ([ccf09f](https://github.com/CuyZ/Valinor/commit/ccf09fd33433e065ca7ad26cc90e433dc8d1ae84))

## Bug Fixes

* Make interface type match undefined object type ([105eef](https://github.com/CuyZ/Valinor/commit/105eef473821f6f6990a080c5e14a78ed5be24db))

## Other

* Change `InvalidParameterIndex` exception inheritance type ([b75adb](https://github.com/CuyZ/Valinor/commit/b75adb7c7ceba2382cab5f265d93ed46bcfd8f4e))
* Introduce layer for object builder arguments ([48f936](https://github.com/CuyZ/Valinor/commit/48f936275e7f8af937e0740cdbbac0f9557ab4a3))
