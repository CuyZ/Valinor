# Using provided normalizer configurators

This library provides a set of [normalizer configurators] out-of-the-box that
can be used to apply common normalization behaviors:

[normalizer configurators]: ./use-normalizer-configurators.md

- [Specifying date time normalization format](#specifying-date-time-normalization-format)

## Specifying date time normalization format

By default, dates will be formatted using the RFC 3339 format. The
`ConvertDateTime` configurator can be used to specify which format to use.

This class can be used either as a configurator for global usage or as an
attribute to target a specific property.

### Global usage as a configurator

```php
use CuyZ\Valinor\Normalizer\Configurator\ConvertDateTime;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

$userAsArray = (new NormalizerBuilder())
    ->configureWith(new ConvertDateTime(\DateTimeInterface::ATOM))
    ->normalizer(Format::array())
    ->normalize($user);

// [
//     'name' => 'Jane Doe',
//     'createdAt' => '2000-01-01T00:00:00+00:00',
// ]
```

### Targeted usage as an attribute

```php
use CuyZ\Valinor\Normalizer\Configurator\ConvertDateTime;
use CuyZ\Valinor\Normalizer\Format;
use CuyZ\Valinor\NormalizerBuilder;

final readonly class User
{
    public function __construct(
        public string $name,

        #[ConvertDateTime(\DateTimeInterface::ATOM)]
        public DateTimeInterface $createdAt,
    ) {}
}

$userAsArray = (new NormalizerBuilder())
    ->normalizer(Format::array())
    ->normalize($user);

// [
//     'name' => 'Jane Doe',
//     'createdAt' => '2000-01-01T00:00:00+00:00',
// ]
```
