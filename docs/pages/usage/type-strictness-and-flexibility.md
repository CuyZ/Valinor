# Type strictness & flexibility

The mapper is sensitive to the types of the data that is recursively populated —
for instance a string `"42"` given to a node that expects an integer will make
the mapping fail because the type is not strictly respected.

Array keys that are not bound to any node are forbidden. Mapping an array
`['foo' => …, 'bar' => …, 'baz' => …]` to an object that needs only `foo` and
`bar` will fail, because `baz` is superfluous. The same rule applies for
shaped arrays.

When mapping to a list, the given array must have sequential integer keys
starting at 0; if any gap or invalid key is found it will fail, like for 
instance trying to map `['foo' => 'foo', 'bar' => 'bar']` to `list<string>`.

Types that are too permissive are not permitted — if the mapper encounters a 
type like `mixed`, `object` or `array` it will fail because those types are not
precise enough.

---

If these limitations are too restrictive, the mapper can be made more flexible
to disable one or several rule(s) declared above.

## Enabling flexible casting

This setting changes the behaviours explained below:

```php
$flexibleMapper = (new \CuyZ\Valinor\MapperBuilder())
    ->enableFlexibleCasting()
    ->mapper();

// ---
// Scalar types will accept non-strict values; for instance an integer
// type will accept any valid numeric value like the *string* "42".

$flexibleMapper->map('int', '42');
// => 42

// ---
// List type will accept non-incremental keys.

$flexibleMapper->map('list<int>', ['foo' => 42, 'bar' => 1337]);
// => [0 => 42, 1 => 1337]

// ---
// If a value is missing in a source for a node that accepts `null`, the
// node will be filled with `null`.

$flexibleMapper->map(
    'array{foo: string, bar: null|string}',
    ['foo' => 'foo'] // `bar` is missing
);
// => ['foo' => 'foo', 'bar' => null]

// ---
// Array and list types will convert `null` or missing values to an empty
// array.

$flexibleMapper->map(
    'array{foo: string, bar: array<string>}',
    ['foo' => 'foo'] // `bar` is missing
);
// => ['foo' => 'foo', 'bar' => []]
```

## Allowing superfluous keys

With this setting enabled, superfluous keys in source arrays will be allowed, 
preventing errors when a value is not bound to any object property/parameter or
shaped array element.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->allowSuperfluousKeys()
    ->mapper()
    ->map(
        'array{foo: string, bar: int}',
        [
            'foo' => 'foo',
            'bar' => 42,
            'baz' => 1337.404, // `baz` will be ignored
        ]
    );
```

## Allowing permissive types

This setting allows permissive types `mixed` and `object` to be used during 
mapping.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->allowPermissiveTypes()
    ->mapper()
    ->map(
        'array{foo: string, bar: mixed}',
        [
            'foo' => 'foo',
            'bar' => 42, // Could be any value
        ]
    );
```

## Allowing dynamic Properties

In some cases it could be required to map dynamic properties if the class implements the magic `__get` and `__set` Methods.
To archive this you should mark the classes with an Interface and all not explicit allowed values would be allowed.

```php
interface MagicGetterSetter {
    public function __get(string $name): string|null;
    public function __set(string $name, string $value): void;
}

class DynamicClass implements MagicGetterSetter {
    /** @var array<string, string> */
    public array $dynamicProperties = [];
    public function __get(string $name): string|null { 
        return $this->dynamicProperties[$name] ?? null; 
    }
    public function __set(string $name, string $value): void {
        $this->dynamicProperties[$name] = $value;
    }
}

(new \CuyZ\Valinor\MapperBuilder())
    ->allowDynamicPropertiesFor(MagicGetterSetter::class)
    ->mapper()
    ->map(
        DynamicClass::class,
        [
            'foo' => 'foo',
            'bar' => 'baz',
        ]
    );

// Result
// DynamicClass::$dynamicProperties' = ['foo' => 'foo','bar' => 'baz']
```
