# Changelog 1.8.0 — 26th of December 2023

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.8.0

## Notable changes

**Normalizer service (serialization)**

This new service can be instantiated with the `MapperBuilder`. It allows
transformation of a given input into scalar and array values, while preserving
the original structure.

This feature can be used to share information with other systems that use a data
format (JSON, CSV, XML, etc.). The normalizer will take care of recursively 
transforming the data into a format that can be serialized.

Below is a basic example, showing the transformation of objects into an array of
scalar values.

```php
namespace My\App;

$normalizer = (new \CuyZ\Valinor\MapperBuilder())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array());

$userAsArray = $normalizer->normalize(
    new \My\App\User(
        name: 'John Doe',
        age: 42,
        country: new \My\App\Country(
            name: 'France',
            countryCode: 'FR',
        ),
    )
);

// `$userAsArray` is now an array and can be manipulated much more
// easily, for instance to be serialized to the wanted data format.
//
// [
//     'name' => 'John Doe',
//     'age' => 42,
//     'country' => [
//         'name' => 'France',
//         'countryCode' => 'FR',
//     ],
// ];
```

A normalizer can be extended by using so-called transformers, which can be
either an attribute or any callable object.

In the example below, a global transformer is used to format any date found by
the normalizer.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerTransformer(
        fn (\DateTimeInterface $date) => $date->format('Y/m/d')
    )
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new \My\App\Event(
            eventName: 'Release of legendary album',
            date: new \DateTimeImmutable('1971-11-08'),
        )
    );

// [
//     'eventName' => 'Release of legendary album',
//     'date' => '1971/11/08',
// ]
```

This date transformer could have been an attribute for a more granular control,
as shown below.

```php
namespace My\App;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class DateTimeFormat
{
    public function __construct(private string $format) {}

    public function normalize(\DateTimeInterface $date): string
    {
        return $date->format($this->format);
    }
}

final readonly class Event
{
    public function __construct(
        public string $eventName,
        #[\My\App\DateTimeFormat('Y/m/d')]
        public \DateTimeInterface $date,
    ) {}
}

(new \CuyZ\Valinor\MapperBuilder())
    ->registerTransformer(\My\App\DateTimeFormat::class)
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(
        new \My\App\Event(
            eventName: 'Release of legendary album',
            date: new \DateTimeImmutable('1971-11-08'),
        )
    );

// [
//     'eventName' => 'Release of legendary album',
//     'date' => '1971/11/08',
// ]
```

---

More features are available, details about it can be found in the documentation.

## Features

* Introduce normalizer service ([1c9368](https://github.com/CuyZ/Valinor/commit/1c9368d79eb0664049d3554fd13d9da8dff08c05))

## Bug Fixes

* Allow leading zeros in numeric string in flexible mode ([f000c1](https://github.com/CuyZ/Valinor/commit/f000c10d07dba9f12d1b7a8e98ff7e5cba44dce8))
* Allow mapping union of scalars and classes ([4f4af0](https://github.com/CuyZ/Valinor/commit/4f4af0ac1b20c7b59bc913a7dfd808dff718b6e2))
* Properly handle single-namespaced classes ([a53ef9](https://github.com/CuyZ/Valinor/commit/a53ef931c565bd3b2917269ca1d79c7f3a5fb672))
* Properly parse class name in same single-namespace ([a462fe](https://github.com/CuyZ/Valinor/commit/a462fe1539a6553af26583fc99f09dfb33b49959))
