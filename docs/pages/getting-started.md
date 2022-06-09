# Getting started

## Installation

```bash
composer require cuyz/valinor
```

## Example

An application must handle the data coming from an external API; the response
has a JSON format and describes a thread and its answers. The validity of this
input is unsure, besides manipulating a raw JSON string is laborious and
inefficient.

```json
{
    "id": 1337,
    "content": "Do you like potatoes?",
    "date": "1957-07-23 13:37:42",
    "answers": [
        {
            "user": "Ella F.",
            "message": "I like potatoes",
            "date": "1957-07-31 15:28:12"
        },
        {
            "user": "Louis A.",
            "message": "And I like tomatoes",
            "date": "1957-08-13 09:05:24"
        }
    ]
}
```

The application must be certain that it can handle this data correctly; wrapping
the input in a value object will help.

---

A schema representing the needed structure must be provided, using classes.

```php
final class Thread
{
    public function __construct(
        public readonly int $id,
        public readonly string $content,
        public readonly DateTimeInterface $date,
        /** @var Answer[] */
        public readonly array $answers, 
    ) {}
}

final class Answer
{
    public function __construct(
        public readonly string $user,
        public readonly string $message,
        public readonly DateTimeInterface $date,
    ) {}
}
```

Then a mapper is used to hydrate a source into these objects.

```php
public function getThread(int $id): Thread
{
    $rawJson = $this->client->request("https://example.com/thread/$id");

    try {   
        return (new \CuyZ\Valinor\MapperBuilder())
            ->mapper()
            ->map(
                Thread::class,
                new \CuyZ\Valinor\Mapper\Source\JsonSource($rawJson)
            );
    } catch (\CuyZ\Valinor\Mapper\MappingError $error) {
        // Do something…
    }
}
```

## Mapping advanced types

Although it is recommended to map an input to a value object, in some cases
mapping to another type can be easier/more flexible.

It is for instance possible to map to an array of objects:

```php
try {
    $objects = (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(
            'array<' . SomeClass::class . '>',
            [/* … */]
        );
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Do something…
}
```

For simple use-cases, an array shape can be used:

```php
try {
    $array = (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(
            'array{foo: string, bar: int}',
            [/* … */]
        );
    
    echo $array['foo'];
    echo $array['bar'] * 2;
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Do something…
}
```
