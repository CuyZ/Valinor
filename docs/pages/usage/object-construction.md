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
the source given to the mapper can match the type of the value — it does not
need to be an array with one value with a key matching the argument/property
name.

This can be useful when the application has control over the format of the 
source given to the mapper, in order to lessen the structure of input.

```php
final readonly class Identifier
{
    public string $value;
}

final readonly class SomeClass
{
    public Identifier $identifier;

    public string $description;
}

$mapper = (new \CuyZ\Valinor\MapperBuilder())->mapper();

$mapper->map(SomeClass::class, [
    'identifier' => [
        // 👎 The `value` key feels a bit excessive
        'value' => 'some-identifier'
    ],
    'description' => 'Lorem ipsum…',
]); 

$mapper->map(SomeClass::class, [
    // 👍 The input has been flattened and is easier to read
    'identifier' => 'some-identifier',
    'description' => 'Lorem ipsum…',
]);
```

## Fixed values from generics

When a constructor argument or a property is typed with a template, and the
generic assigned to this template accepts a single value, this value is given
to the object automatically: it does not need to be part of the source.

This works with integer, float and string values, `true`, `false`, `null` and
enum cases.

### Example: text with a length constraint

The same class can be used for texts with different length constraints; each
property declares its own boundaries, and only the text itself is expected in
the source.

```php
/**
 * @template Min of int
 * @template Max of int
 */
final readonly class Text
{
    public function __construct(
        public string $value,
        /** @var Min */
        public int $min,
        /** @var Max */
        public int $max,
    ) {
        $length = mb_strlen($value);

        if ($length < $min || $length > $max) {
            throw new \InvalidArgumentException(
                "The text must contain between $min and $max characters."
            );
        }
    }
}

final readonly class BlogPost
{
    public function __construct(
        /** @var Text<1, 80> */
        public Text $title,
        /** @var Text<1, 5000> */
        public Text $content,
    ) {}
}

$mapper = (new \CuyZ\Valinor\MapperBuilder())->mapper();

// The `min` and `max` values come from the generics, only the text is needed.
$post = $mapper->map(BlogPost::class, [
    'title' => 'Hello world',
    'content' => 'Lorem ipsum dolor sit amet…',
]);

$post->title->max; // 80
$post->content->max; // 5000
```

### Example: money with a currency

An enum case can be fixed as well. Here, the source only contains amounts,
while each currency is decided by the application: it cannot be changed by the
source.

```php
enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';
}

/**
 * @template C of Currency
 */
final readonly class Money
{
    public function __construct(
        /** @var non-negative-int */
        public int $amount,

        /** @var C */
        public Currency $currency,
    ) {}
}

final readonly class Product
{
    public function __construct(
        public string $name,

        /** @var Money<Currency::EUR> */
        public Money $europeanPrice,

        /** @var Money<Currency::USD> */
        public Money $americanPrice,
    ) {}
}

$product = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(Product::class, [
        'name' => 'Mechanical keyboard',
        // Amounts in cents, the currencies come from the generics
        'europeanPrice' => 4999,
        'americanPrice' => 5499,
    ]);

$product->europeanPrice->currency; // Currency::EUR
$product->americanPrice->currency; // Currency::USD
```
