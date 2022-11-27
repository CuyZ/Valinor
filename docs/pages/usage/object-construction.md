# Object construction

During the mapping, instances of objects are recursively created and hydrated
with values coming from the input.

The values of an object are filled either with a constructor — which is the
recommended way — or using the class properties. If a constructor exists, it
will be used to create the object, otherwise the properties will be filled
directly.

By default, the library will use a native constructor of a class if it is
public; for advanced use cases, the library also allows the [usage of custom
constructors](../how-to/use-custom-object-constructors.md).

## Class with a single value

When an object needs only one value (one constructor argument or one property), 
the source given to the mapper must match the type of the value. See example 
below:

```php
final class Identifier
{
    public readonly string $value;
}

final class SomeClass
{
    public readonly Identifier $identifier;

    public readonly string $description;
}

$mapper = (new \CuyZ\Valinor\MapperBuilder())->mapper();

$mapper->map(SomeClass::class, [
    'identifier' => ['value' => 'some-identifier'], // ❌
    'description' => 'Lorem ipsum…',
]); 

$mapper->map(SomeClass::class, [
    'identifier' => 'some-identifier', // ✅
    'description' => 'Lorem ipsum…',
]);
```
