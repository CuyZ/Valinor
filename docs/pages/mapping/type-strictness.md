# Type strictness

The mapper is sensitive to the types of the data that is recursively populated —
for instance a string `"42"` given to a node that expects an integer will make
the mapping fail because the type is not strictly respected.

Array keys that are not bound to any node are forbidden. Mapping an array
`['foo' => …, 'bar' => …, 'baz' => …]` to an object that needs only `foo` and
`bar` will fail, because `baz` is superfluous. The same rule applies for
shaped arrays.

Types that are too permissive are not permitted — if the mapper encounters a 
type like `mixed`, `object` or `array` it will fail because those types are not
precise enough.

## Flexible mode

If the limitations are too restrictive, the mapper can be made more flexible to
disable all strict rules declared above and enable value casting when possible.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->flexible()
    ->mapper();
    ->map('array{foo: int, bar: bool}', [
        'foo' => '42', // The value will be cast from `string` to `int`
        'bar' => 'true', // The value will be cast from `string` to `bool`
        'baz' => '…', // Will be ignored 
    ]);
```

## When should flexible mode be enabled?

When using this library for a provider application — for instance an API
endpoint that can be called with a JSON payload — it is recommended to use the
strict mode. This ensures that the consumers of the API provide the exact
awaited data structure, and prevents unknown values to be passed.

When using this library as a consumer of an external source, it can make sense
to enable the flexible mode. This allows for instance to convert string numeric
values to integers or to ignore data that is present in the source but not
needed in the application.
