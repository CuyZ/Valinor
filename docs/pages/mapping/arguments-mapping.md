# Mapping arguments of a callable

This library can map the arguments of a callable; it can be used to ensure a
source has the right shape before calling a function/method.

The mapper builder can be configured the same way it would be with a tree 
mapper, for instance to customize the [type strictness](type-strictness.md).

```php
$someFunction = function(string $foo, int $bar): string {
    return "$foo / $bar";
}

try {
    $arguments = (new \CuyZ\Valinor\MapperBuilder())
        ->argumentsMapper()
        ->mapArguments($someFunction, [
            'foo' => 'some value',
            'bar' => 42,
        ]);
    
    // some value / 42
    echo $someFunction(...$arguments);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Do something…
}
```

Any callable can be given to the arguments mapper:

```php
final class SomeController
{
    public static function someAction(string $foo, int $bar): string
    {
        return "$foo / $bar";
    }
}

try {
    $arguments = (new \CuyZ\Valinor\MapperBuilder())
        ->argumentsMapper()
        ->mapArguments(SomeController::someAction(...), [
            'foo' => 'some value',
            'bar' => 42,
        ]);
    
    // some value / 42
    echo SomeController::someAction(...$arguments);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Do something…
}
```
