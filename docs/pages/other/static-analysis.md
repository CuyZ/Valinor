# Static analysis

To help static analysis of a codebase using this library, an extension for
[PHPStan] and a plugin for [Psalm] are provided. They enable these tools to
better understand the behaviour of the mapper.

!!! note

    To activate this feature, the plugin must be registered correctly:

    === "PHPStan"

        ```yaml title="phpstan.neon"
        includes:
            - vendor/cuyz/valinor/qa/PHPStan/valinor-phpstan-configuration.php
        ```

    === "Psalm"

        ```json title="composer.json"
        "autoload-dev": {
            "files": [
                "vendor/cuyz/valinor/qa/Psalm/ValinorPsalmPlugin.php"
            ]
        }
        ```

        ```xml title="psalm.xml"
        <plugins>
            <pluginClass class="CuyZ\Valinor\QA\Psalm\ValinorPsalmPlugin"/>
        </plugins>
        ```

---

Considering at least one of those tools are installed on a project, below are
examples of the kind of errors that would be reported.

**Mapping to an array of classes**

```php
final class SomeClass
{
    public function __construct(
        public readonly string $foo,
        public readonly int $bar,
    ) {}
}

$objects = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(
        'array<' . SomeClass::class . '>',
        [/* … */]
    );

foreach ($objects as $object) {
    // ✅
    echo $object->foo;

    // ✅
    echo $object->bar * 2;

    // ❌ Cannot perform operation between `string` and `int`
    echo $object->foo * $object->bar;

    // ❌ Property `SomeClass::$fiz` is not defined
    echo $object->fiz;
}
```

**Mapping to a shaped array**

```php
$array = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(
        'array{foo: string, bar: int}',
        [/* … */]
    );

// ✅
echo $array['foo'];

// ❌ Expected `string` but got `int`
echo strtolower($array['bar']);

// ❌ Cannot perform operation between `string` and `int`
echo $array['foo'] * $array['bar'];

// ❌ Offset `fiz` does not exist on array
echo $array['fiz']; 
```

**Mapping arguments of a callable**

```php
$someFunction = function(string $foo, int $bar): string {
    return "$foo / $bar";
};

$arguments = (new \CuyZ\Valinor\MapperBuilder())
    ->argumentsMapper()
    ->mapArguments($someFunction, [
        'foo' => 'some value',
        'bar' => 42,
    ]);

// ✅ Arguments have a correct shape, no error reported
echo $someFunction(...$arguments);
```
