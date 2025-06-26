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

## About mapper and normalizer purity

The mapper and normalizer are designed to be pure, meaning they do not have side
effects and will always return the same result for the same input. For more
information, see the [definition of a pure function].

It is recommended to keep purity in mind when applications are being developed,
by using the `@pure` annotation that is handled by [PHPStan] and [Psalm].

Mapping user-provided data is, in itself, a potentially dangerous operation: by
relying only on pure functions/types for mapping, you ensure that malicious user
input won't cause side effects when `TreeMapper#map()` is called. You are free to
suppress/ignore the given purity guidelines, at the cost of an expanded attack
surface.

There are cases where the purity analysis can return false-positives, which can be 
easily ignored, for instance by using the
[`@phpstan-ignore`](https://phpstan.org/user-guide/ignoring-errors#ignoring-in-code-using-phpdocs)
and
[`@psalm-suppress`](https://psalm.dev/docs/running_psalm/dealing_with_code_issues/#docblock-suppression)
annotations.

!!! note

    If too many purity errors concerning the mapper or normalizer are reported,
    this library provides a way to ignore them globally:

    === "PHPStan"

        ```yaml title="phpstan.neon"
        includes:
            - vendor/cuyz/valinor/qa/PHPStan/valinor-phpstan-suppress-pure-errors.php
        ```

    === "Psalm"

        ```xml title="psalm.xml"
        <xi:include href="vendor/cuyz/valinor/qa/Psalm/valinor-psalm-suppress-pure-errors.xml" xmlns:xi="http://www.w3.org/2001/XInclude"/>
        ```

[definition of a pure function]: https://en.wikipedia.org/wiki/Pure_function
