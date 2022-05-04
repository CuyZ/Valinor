Valinor • PHP object mapper with strong type support
====================================================

[![Total Downloads](http://poser.pugx.org/cuyz/valinor/downloads)][link-packagist]
[![Latest Stable Version](http://poser.pugx.org/cuyz/valinor/v)][link-packagist]
[![PHP Version Require](http://poser.pugx.org/cuyz/valinor/require/php)][link-packagist]

[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FCuyZ%2FValinor%2Fmaster)](https://dashboard.stryker-mutator.io/reports/github.com/CuyZ/Valinor/master)

Valinor is a PHP library that helps to map any input into a strongly-typed value
object structure.

The conversion can handle native PHP types as well as other well-known advanced
type annotations like array shapes, generics and more.

## Why?

There are many benefits of using value objects instead of plain arrays and
scalar values in a modern codebase, among which:

1. **Data and behaviour encapsulation** — locks an object's behaviour inside its
   class, preventing it from being scattered across the codebase.
2. **Data validation** — guarantees the valid state of an object.
3. **Immutability** — ensures the state of an object cannot be changed during
   runtime.

When mapping any source to an object structure, this library will ensure that
all input values are properly converted to match the types of the nodes — class
properties or method parameters. Any value that cannot be converted to the
correct type will trigger an error and prevent the mapping from completing.

These checks guarantee that if the mapping succeeds, the object structure is
perfectly valid, hence there is no need for further validation nor type
conversion: the objects are ready to be used.

### Static analysis

A strongly-typed codebase allows the usage of static analysis tools like
[PHPStan] and [Psalm] that can identify issues in a codebase without running it.

Moreover, static analysis can help during a refactoring of a codebase with tools
like an IDE or [Rector].

## Usage

### Installation

```bash
composer require cuyz/valinor
```

### Example

An application must handle the data coming from an external API; the response
has a JSON format and describes a thread and its answers. The validity of this
input is unsure, besides manipulating a raw JSON string is laborious and
inefficient.

```json
{
    "id": 1337,
    "content": "Do you like potatoes?",
    "date": "1957-07-23 13:37:42",
    "answers": [
        {
            "user": "Ella F.",
            "message": "I like potatoes",
            "date": "1957-07-31 15:28:12"
        },
        {
            "user": "Louis A.",
            "message": "And I like tomatoes",
            "date": "1957-08-13 09:05:24"
        }
    ]
}
```

The application must be certain that it can handle this data correctly; wrapping
the input in a value object will help.

---

A schema representing the needed structure must be provided, using classes.

```php
final class Thread
{
    public function __construct(
        public readonly int $id,
        public readonly string $content,
        public readonly DateTimeInterface $date,
        /** @var Answer[] */
        public readonly array $answers, 
    ) {}
}

final class Answer
{
    public function __construct(
        public readonly string $user,
        public readonly string $message,
        public readonly DateTimeInterface $date,
    ) {}
}
```

Then a mapper is used to hydrate a source into these objects.

```php
public function getThread(int $id): Thread
{
    $rawJson = $this->client->request("https://example.com/thread/$id");

    try {   
        return (new \CuyZ\Valinor\MapperBuilder())
            ->mapper()
            ->map(
                Thread::class,
                new \CuyZ\Valinor\Mapper\Source\JsonSource($rawJson)
            );
    } catch (\CuyZ\Valinor\Mapper\MappingError $error) {
        // Do something…
    }
}
```

### Mapping advanced types

Although it is recommended to map an input to a value object, in some cases 
mapping to another type can be easier/more flexible.

It is for instance possible to map to an array of objects:

```php
try {
    $objects = (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(
            'array<' . SomeClass::class . '>',
            [/* … */]
        );
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Do something…
}
```

For simple use-cases, an array shape can be used:

```php
try {
    $array = (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(
            'array{foo: string, bar: int}',
            [/* … */]
        );
    
    echo $array['foo'];
    echo $array['bar'] * 2;
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    // Do something…
}
```

### Validation

The source given to a mapper can never be trusted, this is actually the very
goal of this library: transforming an unstructured input to a well-defined
object structure. If the mapper cannot guess how to cast a certain value, it
means that it is not able to guarantee the validity of the desired object thus
it will fail.

Any issue encountered during the mapping will add an error to an upstream
exception of type `\CuyZ\Valinor\Mapper\MappingError`. It is therefore always
recommended wrapping the mapping function call with a try/catch statement and
handle the error properly.

More specific validation should be done in the constructor of the value object,
by throwing an exception if something is wrong with the given data. A good
practice would be to use lightweight validation tools like [Webmozart Assert].

When the mapping fails, the exception gives access to the root node. This
recursive object allows retrieving all needed information through the whole
mapping tree: path, values, types and messages, including the issues that caused
the exception.

```php
final class SomeClass
{
    public function __construct(private string $someValue)
    {
        Assert::startsWith($someValue, 'foo_');
    }
}

try {
   (new \CuyZ\Valinor\MapperBuilder())
       ->mapper()
       ->map(
           SomeClass::class,
           ['someValue' => 'bar_baz']
       );
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    $node = $error->node();

    // The name of a node can be accessed 
    $name = $node->name();

    // The logical path of a node contains dot separated names of its parents
    $path = $node->path();
    
    // The type of the node can be cast to string to enhance suggestion messages 
    $type = (string)$node->type();

    // If the node is a branch, its children can be recursively accessed
    foreach ($node->children() as $child) {
        // Do something…  
    }
    
    // Get flatten list of all messages through the whole nodes tree
    $messages = new \CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener($node);
    
    // If only errors are wanted, they can be filtered
    $errorMessages = $messages->errors();

    // Should print something similar to:
    // > Expected a value to start with "foo_". Got: "bar_baz"
    foreach ($errorsMessages as $message) {
        echo $message;
    }
}
```

### Message customization / translation

When working with messages, it can sometimes be useful to customize the content
of a message — for instance to translate it.

The helper class `\CuyZ\Valinor\Mapper\Tree\Message\MessageMapFormatter` can be
used to provide a list of new formats. It can be instantiated with an array 
where each key represents either:

- The code of the message to be replaced
- The content of the message to be replaced
- The class name of the message to be replaced

If none of those is found, the content of the message will stay unchanged unless
a default one is given to the class.

If one of these keys is found, the array entry will be used to replace the
content of the message. This entry can be either a plain text or a callable that
takes the message as a parameter and returns a string; it is for instance
advised to use a callable in cases where a translation service is used — to
avoid useless greedy operations.

In any case, the content can contain placeholders that will automatically be
replaced by, in order:

1. The original code of the message
2. The original content of the message
3. A string representation of the node type
4. The name of the node
5. The path of the node

```php
try {
    (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(SomeClass::class, [/* … */]);
} catch (\CuyZ\Valinor\Mapper\MappingError $error) {
    $node = $error->node();
    $messages = new \CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener($node);

    $formatter = (new \CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageMapFormatter([
        // Will match if the given message has this exact code
        'some_code' => 'new content / previous code was: %1$s',
    
        // Will match if the given message has this exact content
        'Some message content' => 'new content / previous message: %2$s',
    
        // Will match if the given message is an instance of `SomeError`
        SomeError::class => '
            - Original code of the message: %1$s
            - Original content of the message: %2$s
            - Node type: %3$s
            - Node name: %4$s
            - Node path: %5$s
        ',
    
        // A callback can be used to get access to the message instance
        OtherError::class => function (NodeMessage $message): string {
            if ((string)$message->type() === 'string|int') {
                // …
            }
    
            return 'Some message content';
        },
    
        // For greedy operation, it is advised to use a lazy-callback
        'foo' => fn () => $this->translator->translate('foo.bar'),
    ]))
        ->defaultsTo('some default message')
        // …or…
        ->defaultsTo(fn () => $this->translator->translate('default_message'));

    foreach ($messages as $message) {
        echo $formatter->format($message);    
    }
}
```

### Source

Any source can be given to the mapper, be it an array, some json, yaml or even a file:

```php
function map($source) {
    return (new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(SomeClass::class, $source);
}

map(\CuyZ\Valinor\Mapper\Source\Source::array($someData));

map(\CuyZ\Valinor\Mapper\Source\Source::json($jsonString));

map(\CuyZ\Valinor\Mapper\Source\Source::yaml($yamlString));

// File containing valid Json or Yaml content and with valid extension
map(new \CuyZ\Valinor\Mapper\Source\Source::file(
    new SplFileObject('path/to/my/file.json')
));
```

#### Modifiers

Sometimes the source is not in the same format and/or organised in the same
way as a value object. Modifiers can be used to change a source before the
mapping occurs.

##### Camel case keys

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

##### Path mapping

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

#### Custom source

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

$source = \CuyZ\Valinor\Mapper\Source\Source::iterable(new AcmeSource(['value' => 'foo']))
    ->camelCaseKeys();

(new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(SomeClass::class, $source);
```

### Construction strategy

During the mapping, instances of objects are recursively created and hydrated
with transformed values. Construction strategies will determine what values are
needed and how an object is built.

#### Native constructor

If a constructor exists and is public, its arguments will determine which values
are needed from the input.

```php
final class SomeClass
{
    public function __construct(
        public readonly string $foo,
        public readonly int $bar,
    ) {}
}
```

#### Custom constructor

An object may have custom ways of being created, in such cases these
constructors need to be registered to the mapper to be used. A constructor is a
callable that can be either:

1. A named constructor, also known as a static factory method
2. The method of a service — for instance a repository
3. A "callable object" — a class that declares an `__invoke` method
4. Any other callable — including anonymous functions

In any case, the return type of the callable will be resolved by the mapper to
know when to use it. Any argument can be provided and will automatically be
mapped using the given source. These arguments can then be used to instantiate 
the object in the desired way.

Registering any constructor will disable the native constructor — the
`__construct` method — of the targeted class. If for some reason it still needs 
to be handled as well, the name of the class must be given to the 
registration method.

```php
(new \CuyZ\Valinor\MapperBuilder())
    ->registerConstructor(
        // Allow the native constructor to be used
        Color::class,

        // Register a named constructor
        Color::fromHex(...),
        // …or for PHP < 8.1:
        [Color::class, 'fromHex'],

        /**
         * An anonymous function can also be used, for instance when the desired
         * object is an external dependency that cannot be modified.
         * 
         * @param 'red'|'green'|'blue' $color
         * @param 'dark'|'light' $darkness
         */
        function (string $color, string $darkness): stdClass {
            $main = $darkness === 'dark' ? 128 : 255;
            $other = $darkness === 'dark' ? 0 : 128;
 
            return new Color(
                $color === 'red' ? $main : $other,
                $color === 'green' ? $main : $other,
                $color === 'blue' ? $main : $other,
            );
        }
    )
    ->mapper()
    ->map(Color::class, [/* … */]);

final class Color
{
    /**
     * @param int<0, 255> $red
     * @param int<0, 255> $green
     * @param int<0, 255> $blue
     */
    public function __construct(
        public readonly int $red,
        public readonly int $green,
        public readonly int $blue
    ) {}

    /**
     * @param non-empty-string $hex
     */
    public static function fromHex(string $hex): self
    {
        if (strlen($hex) !== 6) {
            throw new DomainException('Must be 6 characters long');
        }

        /** @var int<0, 255> $red */
        $red = hexdec(substr($hex, 0, 2));
        /** @var int<0, 255> $green */
        $green = hexdec(substr($hex, 2, 2));
        /** @var int<0, 255> $blue */
        $blue = hexdec(substr($hex, 4, 2));

        return new self($red, $green, $blue);
    }
}
```

#### Properties

If no constructor is registered, properties will determine which values are
needed from the input.

```php
final class SomeClass
{
    public readonly string $foo;

    public readonly int $bar;
}
```

### Inferring interfaces

When the mapper meets an interface, it needs to understand which implementation
(a class that implements this interface) will be used — this information must be
provided in the mapper builder, using the method `infer()`.

The callback given to this method must return the name of a class that 
implements the interface. Any arguments can be required by the callback; they
will be mapped properly using the given source.

```php
$mapper = (new \CuyZ\Valinor\MapperBuilder())
    ->infer(UuidInterface::class, fn () => MyUuid::class)
    ->infer(SomeInterface::class, fn (string $type) => match($type) {
        'first' => FirstImplementation::class,
        'second' => SecondImplementation::class,
        default => throw new DomainException("Unhandled type `$type`.")
    })->mapper();

// Will return an instance of `FirstImplementation`
$mapper->map(SomeInterface::class, [
    'type' => 'first',
    'uuid' => 'a6868d61-acba-406d-bcff-30ecd8c0ceb6',
    'someString' => 'foo',
]);

// Will return an instance of `SecondImplementation`
$mapper->map(SomeInterface::class, [
    'type' => 'second',
    'uuid' => 'a6868d61-acba-406d-bcff-30ecd8c0ceb6',
    'someInt' => 42,
]);

interface SomeInterface {}

final class FirstImplementation implements SomeInterface
{
    public readonly UuidInterface $uuid;

    public readonly string $someString;
}

final class SecondImplementation implements SomeInterface
{
    public readonly UuidInterface $uuid;

    public readonly int $someInt;
}
```

### Modifiers

Sometimes the source is not in the same format and/or organised in the same
way as a value object. Modifiers can be used to change a source before the 
mapping occurs.

#### Camel case keys

This modifier recursively forces all keys to be in camelCase format.

```php
final class SomeClass
{
    public readonly string $someValue;
}

$source = new \CuyZ\Valinor\Mapper\Source\Modifier\CamelCaseKeys([
    'some_value' => 'foo',
    // …or…
    'some-value' => 'foo',
    // …or…
    'some value' => 'foo',
    // …will be replaced by `['someValue' => 'foo']`
]);

(new \CuyZ\Valinor\MapperBuilder())
        ->mapper()
        ->map(SomeClass::class, $source);
```

## Handled types

To prevent conflicts or duplication of the type annotations, this library tries
to handle most of the type annotations that are accepted by [PHPStan] and
[Psalm].

### Scalar

```php
final class SomeClass
{
    public function __construct(
        private bool $boolean,

        private float $float,

        private int $integer,

        /** @var positive-int */
        private int $positiveInteger,

        /** @var negative-int */
        private int $negativeInteger,

        /** @var int<-42, 1337> */
        private int $integerRange,

        /** @var int<min, 0> */
        private int $integerRangeWithMinRange,

        /** @var int<0, max> */
        private int $integerRangeWithMaxRange,

        private string $string,
        
        /** @var non-empty-string */
        private string $nonEmptyString,

        /** @var class-string */
        private string $classString,

        /** @var class-string<SomeInterface> */
        private string $classStringOfAnInterface,
    ) {}
}
```

### Object

```php
final class SomeClass
{
    public function __construct(
        private SomeClass $class,

        private DateTimeInterface $interface,

        /** @var SomeInterface&AnotherInterface */
        private object $intersection,

        /** @var SomeCollection<SomeClass> */
        private SomeCollection $classWithGeneric,
    ) {}
}

/**
 * @template T of object 
 */
final class SomeCollection
{
    public function __construct(
        /** @var array<T> */
        private array $objects,
    ) {}
}
```

### Array & lists

```php
final class SomeClass
{
    public function __construct(
        /** @var string[] */
        private array $simpleArray,

        /** @var array<string> */
        private array $arrayOfStrings,

        /** @var array<string, SomeClass> */
        private array $arrayOfClassWithStringKeys,

        /** @var array<int, SomeClass> */
        private array $arrayOfClassWithIntegerKeys,

        /** @var non-empty-array<string> */
        private array $nonEmptyArrayOfStrings,

        /** @var non-empty-array<string, SomeClass> */
        private array $nonEmptyArrayWithStringKeys,
        
        /** @var list<string> */
        private array $listOfStrings,
        
        /** @var non-empty-list<string> */
        private array $nonEmptyListOfStrings,

        /** @var array{foo: string, bar: int} */
        private array $shapedArray,

        /** @var array{foo: string, bar?: int} */
        private array $shapedArrayWithOptionalElement,

        /** @var array{string, bar: int} */
        private array $shapedArrayWithUndefinedKey,
    ) {}
}
```

### Union

```php
final class SomeClass
{
    public function __construct(
        private int|string $simpleUnion,
        
        /** @var class-string<SomeInterface|AnotherInterface> */
        private string $unionOfClassString,
        
        /** @var array<SomeInterface|AnotherInterface> */
        private array $unionInsideArray,
        
        /** @var int|true */
        private int|bool $unionWithLiteralTrueType;
        
        /** @var int|false */
        private int|bool $unionWithLiteralFalseType;
        
        /** @var 404.42|1337.42 */
        private string $unionOfFloatValues,
        
        /** @var 42|1337 */
        private string $unionOfIntegerValues,
        
        /** @var 'foo'|'bar' */
        private string $unionOfStringValues,
    ) {}
}
```

## Static analysis

To help static analysis of a codebase using this library, an extension for
[PHPStan] and a plugin for [Psalm] are provided. They enable these tools to
better understand the behaviour of the mapper.

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

---

To activate this feature, the configuration must be updated for the installed
tool(s):

**PHPStan**

```yaml
includes:
    - vendor/cuyz/valinor/qa/PHPStan/valinor-phpstan-configuration.php
```

**Psalm**

```xml

<plugins>
    <plugin filename="vendor/cuyz/valinor/qa/Psalm/Plugin/TreeMapperPsalmPlugin.php"/>
</plugins>
```

[PHPStan]: https://phpstan.org/

[Psalm]: https://psalm.dev/

[Rector]: https://github.com/rectorphp/rector

[Webmozart Assert]: https://github.com/webmozarts/assert

[link-packagist]: https://packagist.org/packages/cuyz/valinor
