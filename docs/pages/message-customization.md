# Message customization

The content of a message can be changed to fit custom use cases; it can contain
placeholders that will be replaced with useful information.

The placeholders below are always available; even more may be used depending
on the original message.

| Placeholder          | Description                                          |
|----------------------|:-----------------------------------------------------|
| `{message_code}`     | the code of the message                              |
| `{node_name}`        | name of the node to which the message is bound       |
| `{node_path}`        | path of the node to which the message is bound       |
| `{node_type}`        | type of the node to which the message is bound       |
| `{original_value}`   | the source value that was given to the node          |
| `{original_message}` | the original message before being customized         |

Usage:

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

!!! warning
    
    If the `intl` extension is not installed, a shim will be
    available to replace the placeholders, but it won't handle advanced syntax as
    described above.

## Deeper message customization / translation

For deeper message changes, formatters can be used — for instance to translate
content.

### Translation

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

### Replacement map

The formatter `MessageMapFormatter` can be used to provide a list of messages
replacements. It can be instantiated with an array where each key represents
either:

- The code of the message to be replaced
- The body of the message to be replaced
- The class name of the message to be replaced

If none of those is found, the content of the message will stay unchanged unless
a default one is given to the class.

If one of these keys is found, the array entry will be used to replace the
content of the message. This entry can be either a plain text or a callable that
takes the message as a parameter and returns a string; it is for instance
advised to use a callable in cases where a custom translation service is used —
to avoid useless greedy operations.

In any case, the content can contain placeholders as described
[above](#message-customization).

```php
(new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter([
    // Will match if the given message has this exact code
    'some_code' => 'New content / code: {message_code}',

    // Will match if the given message has this exact content
    'Some message content' => 'New content / previous: {original_message}',

    // Will match if the given message is an instance of `SomeError`
    SomeError::class => 'New content / value: {original_value}',

    // A callback can be used to get access to the message instance
    OtherError::class => function (NodeMessage $message): string {
        if ($message->path() === 'foo.bar') {
            return 'Some custom message';
        }

        return $message->body();
    },

    // For greedy operation, it is advised to use a lazy-callback
    'foo' => fn () => $this->customTranslator->translate('foo.bar'),
]))
    ->defaultsTo('some default message')
    // …or…
    ->defaultsTo(fn () => $this->customTranslator->translate('default_message'))
    ->format($message);
```

### Several formatters

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
