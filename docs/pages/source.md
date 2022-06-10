# Source

Any source can be given to the mapper, be it an array, some json, yaml or even a
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
    /** @var City[] */
    public readonly array $cities;
}

final class City
{
    public readonly string $name;
}

$source = \CuyZ\Valinor\Mapper\Source\Source::array([
        'towns' => [
            ['label' => 'Ankh Morpork'],
            ['label' => 'Minas Tirith'],
        ],
    ])
    ->map([
        'towns' => 'cities',
        'towns.*.label' => 'name',
   ]);

// After modification this is what the source will look like:
[
    'cities' => [
        ['name' => 'Ankh Morpork'],
        ['name' => 'Minas Tirith'],
    ],
];

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(Country::class, $source);
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
        new AcmeSource(['value' => 'foo'])
    )->camelCaseKeys();

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(SomeClass::class, $source);
```
