# Changelog 1.11.0 — 27th of March 2024

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/1.11.0

## Notable changes

**Improvement of union types narrowing**

The algorithm used by the mapper to narrow a union type has been greatly
improved, and should cover more edge-cases that would previously prevent the
mapper from performing well.

If an interface, a class or a shaped array is matched by the input, it will take
precedence over arrays or scalars.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(
        signature: 'array<int>|' . Color::class,
        source: [
            'red' => 255,
            'green' => 128,
            'blue' => 64,
        ],
    ); // Returns an instance of `Color`
```

When superfluous keys are allowed, if the input matches several interfaces,
classes or shaped array, the one with the most children node will be
prioritized, as it is considered the most specific type:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->allowSuperfluousKeys()
    ->mapper()
    ->map(
        // Even if the first shaped array matches the input, the second one is
        // used because it's more specific.
        signature: 'array{foo: int}|array{foo: int, bar: int}',
        source: [
            'foo' => 42,
            'bar' => 1337,
        ],
    );
```

If the input matches several types within the union, a collision will occur and
cause the mapper to fail:

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(
        // Even if the first shaped array matches the input, the second one is
        // used because it's more specific.
        signature: 'array{red: int, green: int, blue: int}|' . Color::class,
        source: [
            'red' => 255,
            'green' => 128,
            'blue' => 64,
        ],
    );

// ⚠️ Invalid value array{red: 255, green: 128, blue: 64}, it matches at
//    least two types from union.
```

**Introducing `AsTransformer` attribute**

After the introduction of the [`Constructor` attribute](https://github.com/CuyZ/Valinor/commit/d86295c2fe2e7ea7ed37d00fd39f20f31a694129)
used for the mapper, the new `AsTransformer` attribute is now available for the
normalizer to ease the registration of a transformer.

```php
namespace My\App;

#[\CuyZ\Valinor\Normalizer\AsTransformer]
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
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::array())
    ->normalize(new \My\App\Event(
        eventName: 'Release of legendary album',
        date: new \DateTimeImmutable('1971-11-08'),
    ));

// [
//     'eventName' => 'Release of legendary album',
//     'date' => '1971/11/08',
// ]
```

### Features

* Improve union type narrowing during mapping ([f73158](https://github.com/CuyZ/Valinor/commit/f73158657c8e876e8b3142d6064878b6af1c3525))
* Introduce `AsTransformer` attribute ([13b6d0](https://github.com/CuyZ/Valinor/commit/13b6d0186adbaafdd4474fc1dbdc725aa3ad8585))

### Bug Fixes

* Handle single array mapping when a superfluous value is present ([86d021](https://github.com/CuyZ/Valinor/commit/86d021a2fdd9932554a2e41a08d19d8801371f6f))
* Properly handle `ArrayObject` normalization ([4f555d](https://github.com/CuyZ/Valinor/commit/4f555d9bc9de996b5c577396076b674e17fbb429))
* Properly handle class type with matching name and namespace ([0f5e96](https://github.com/CuyZ/Valinor/commit/0f5e96e6a7567465c623914d038342ba56875595))
* Properly handle nested unresolvable type during mapping ([194706](https://github.com/CuyZ/Valinor/commit/19470614fa99b97341975a44fdf034ff6e9a6f6d))
* Strengthen type tokens extraction ([c9dc97](https://github.com/CuyZ/Valinor/commit/c9dc975357319ae77d352e17979888b08690ddbc))

### Other

* Reduce number of calls to class autoloader during type parsing ([0f0e35](https://github.com/CuyZ/Valinor/commit/0f0e35e71925bfb31ecd916766aca5492f12863d))
* Refactor generic types parsing and checking ([ba6770](https://github.com/CuyZ/Valinor/commit/ba67703ed19f5bf35467cc54606ebfd373e6aedc))
* Separate native type and docblock type for property and parameter ([37993b](https://github.com/CuyZ/Valinor/commit/37993b64a6eb04dc0aee79e03f2ddb4f86ff9c3a))
