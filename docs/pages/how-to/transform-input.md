# Transforming input

Any source can be given to the mapper, be it an array, some JSON, YAML or even a
file:

```php
$mapper = (new \CuyZ\Valinor\MapperBuilder())->mapper();

$mapper->map(
    SomeClass::class,
    \CuyZ\Valinor\Mapper\Source\Source::array($someData)
);

$mapper->map(
    SomeClass::class,
    \CuyZ\Valinor\Mapper\Source\Source::json($jsonString)
);

$mapper->map(
    SomeClass::class,
    \CuyZ\Valinor\Mapper\Source\Source::yaml($yamlString)
);

$mapper->map(
    SomeClass::class,
    // File containing valid Json or Yaml content and with valid extension
    \CuyZ\Valinor\Mapper\Source\Source::file(
        new SplFileObject('path/to/my/file.json')
    )
);
```

!!! info

    JSON or YAML given to a source may be invalid, in which case an exception 
    can be caught and manipulated.

    ```php
    try {
        $source = \CuyZ\Valinor\Mapper\Source\Source::json('invalid JSON');
    } catch (\CuyZ\Valinor\Mapper\Source\Exception\InvalidSource $exception) {
        // Let the application handle the exception in the desired way.
        // It is possible to get the original source with `$exception->source()`
    }
    ```

## Modifiers

Sometimes the source is not in the same format and/or organised in the same
way as a value object. Modifiers can be used to change a source before the
mapping occurs.

### Camel case keys

This modifier recursively forces all keys to be in camelCase format.

```php
final class SomeClass
{
    public readonly string $someValue;
}

$source = \CuyZ\Valinor\Mapper\Source\Source::array([
        'some_value' => 'foo',
        // …or…
        'some-value' => 'foo',
        // …or…
        'some value' => 'foo',
        // …will be replaced by `['someValue' => 'foo']`
    ])
    ->camelCaseKeys();

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(SomeClass::class, $source);
```

### Path mapping

This modifier can be used to change paths in the source data using a dot
notation.

The mapping is done using an associative array of path mappings. This array must
have the source path as key and the target path as value.

The source path uses the dot notation (eg `A.B.C`) and can contain one `*` for
array paths (eg `A.B.*.C`).

```php
final class Country
{
    /** @var non-empty-string */
    public readonly string $name;

    /** @var list<City> */
    public readonly array $cities;
}

final class City
{
    /** @var non-empty-string */
    public readonly string $name;

    public readonly DateTimeZone $timeZone;
}

$source = \CuyZ\Valinor\Mapper\Source\Source::array([
    'identification' => 'France',
    'towns' => [
        [
            'label' => 'Paris',
            'timeZone' => 'Europe/Paris',
        ],
        [
            'label' => 'Lyon',
            'timeZone' => 'Europe/Paris',
        ],
    ],
])->map([
    'identification' => 'name',
    'towns' => 'cities',
    'towns.*.label' => 'name',
]);

// After modification this is what the source will look like:
// [
//     'name' => 'France',
//     'cities' => [
//         [
//             'name' => 'Paris',
//             'timeZone' => 'Europe/Paris',
//         ],
//         [
//             'name' => 'Lyon',
//             'timeZone' => 'Europe/Paris',
//         ],
//     ],
// ];

(new \CuyZ\Valinor\MapperBuilder())->mapper()->map(Country::class, $source);
```

## Custom source

The source is just an iterable, so it's easy to create a custom one.
It can even be combined with the provided builder.

```php
final class AcmeSource implements IteratorAggregate
{
    private iterable $source;
    
    public function __construct(iterable $source)
    {
        $this->source = $this->doSomething($source);
    }
    
    private function doSomething(iterable $source): iterable
    {
        // Do something with $source
        
        return $source;
    }
    
    public function getIterator()
    {
        yield from $this->source;
    }
}

$source = \CuyZ\Valinor\Mapper\Source\Source::iterable(
    new AcmeSource([
        'valueA' => 'foo',
        'valueB' => 'bar',
    ])
)->camelCaseKeys();

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(SomeClass::class, $source);
```
