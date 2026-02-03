# Using normalizer configurators

A `NormalizerBuilderConfigurator` is a reusable piece of configuration logic
that can be applied to a `NormalizerBuilder` instance. This is useful when the
same normalization configuration needs to be applied in multiple places across
an application, or when configuration logic needs to be distributed as a
package.

In the example below, we apply two configuration settings to a
`NormalizerBuilder` inside a single class, but this could contain any number of
customizations, depending on the needs of the application.

```php
namespace My\App;

use CuyZ\Valinor\NormalizerBuilder;
use CuyZ\Valinor\Normalizer\Configurator\NormalizerBuilderConfigurator;

final class ApiResponseConfigurator implements NormalizerBuilderConfigurator
{
    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder
            ->registerTransformer(
                fn (\DateTimeInterface $date) => $date->format('Y-m-d')
            )
            ->registerTransformer(
                fn (\My\App\Money $money) => [
                    'amount' => $money->amount,
                    'currency' => $money->currency->value,
                ]
            );
    }
}
```

This configurator can be registered within the `NormalizerBuilder` instance:

```php
$json = (new \CuyZ\Valinor\NormalizerBuilder())
    ->configureWith(new \My\App\ApiResponseConfigurator())
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
    ->normalize($someObject);
```

## Composing multiple configurators

Multiple configurators can be combined to compose the final configuration. Each
configurator is applied in order, keeping each one focused on a single concern.

```php
namespace My\App;

use CuyZ\Valinor\NormalizerBuilder;
use CuyZ\Valinor\Normalizer\Configurator\NormalizerBuilderConfigurator;

final class DomainObjectConfigurator implements NormalizerBuilderConfigurator
{
    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder
            ->registerTransformer(
                fn (\DateTimeInterface $date) => $date->format('Y-m-d')
            )
            ->registerTransformer(
                fn (\My\App\Money $money) => [
                    'amount' => $money->amount,
                    'currency' => $money->currency->value,
                ]
            );
    }
}

final class SensitiveDataConfigurator implements NormalizerBuilderConfigurator
{
    public function configureNormalizerBuilder(NormalizerBuilder $builder): NormalizerBuilder
    {
        return $builder
            ->registerTransformer(
                fn (\My\App\EmailAddress $email) => '***@' . $email->domain()
            );
    }
}

$json = (new \CuyZ\Valinor\NormalizerBuilder())
    ->configureWith(
        new \My\App\DomainObjectConfigurator(),
        new \My\App\SensitiveDataConfigurator(),
    )
    ->normalizer(\CuyZ\Valinor\Normalizer\Format::json())
    ->normalize($someObject);
```

This approach makes each configurator easier to test and reuse independently.
