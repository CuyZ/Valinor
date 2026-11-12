# Changelog 2.7.0 — 12th of November 2026

!!! info inline end "[See release on GitHub]"
    [See release on GitHub]: https://github.com/CuyZ/Valinor/releases/tag/2.7.0

## Notable changes

### Fixed values from generics

When a constructor argument or a property is typed with a template of the class
(or of a registered constructor), and the generic assigned to this template
accepts a single value, this value is now given to the object automatically: it
does not need to be part of the source anymore.

This works with integer, float and string values, `true`, `false`, `null` and
enum cases.

The same class can then be used for texts with different length constraints;
each property declares its own boundaries, and only the text itself is expected
in the source:

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

$post = (new \CuyZ\Valinor\MapperBuilder())
    ->mapper()
    ->map(BlogPost::class, [
        // The `min` and `max` values come from the generics
        'title' => 'Hello world',
        'content' => 'Lorem ipsum dolor sit amet…',
    ]);

$post->title->max; // 80
$post->content->max; // 5000
```

An enum case can be fixed as well. Here, the source only contains amounts, while
each currency is decided by the application: it cannot be changed by the source.

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

Only a type that is exactly the template is filled: a type such as `Min|null` or
`list<Min>`, or a value written directly in the type, is still expected in the
source. The source is mapped as if the fixed arguments did not exist, and they
are hidden from mapping errors.

---

### Features

* Fill constructor arguments and properties fixed by generics ([73ff0d](https://github.com/CuyZ/Valinor/commit/73ff0d31b9997d38f089b504952e090fb336804c))

### Bug Fixes

* Call a converter only once per node ([25eff3](https://github.com/CuyZ/Valinor/commit/25eff35cd9aa8414ceabb021d1af6ea0c55e9efc))
* Call template converters only for targets within their bound ([589fa0](https://github.com/CuyZ/Valinor/commit/589fa07323341a0c55289b483695fc4d2faccc60))
* Keep default value of single argument missing from HTTP request ([20a362](https://github.com/CuyZ/Valinor/commit/20a362bffeaa4fd459f5e64c5aab20f049e20da4))
* Keep error path of keys named like a flattened single argument ([23b988](https://github.com/CuyZ/Valinor/commit/23b9880b2f2fc933f318bc255bdbb76037d94f81))
* Resolve namespace imports used as qualified name prefix ([7a44df](https://github.com/CuyZ/Valinor/commit/7a44df906ca4d267e41018975790638831a520e8))
