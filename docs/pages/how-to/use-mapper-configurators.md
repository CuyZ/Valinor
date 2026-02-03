# Using mapper builder configurators

A `MapperBuilderConfigurator` is a reusable piece of configuration logic that
can be applied to a `MapperBuilder` instance. This is useful when the same
mapping configuration needs to be applied in multiple places across an
application, or when configuration logic needs to be distributed as a package.

In the example below, we apply two configuration settings to a `MapperBuilder`
inside a single class, but this could contain any number of customizations,
depending on the needs of the application.

```php
namespace My\App;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Mapper\Configurator\MapperBuilderConfigurator;

final class ApplicationMappingConfigurator implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder
            ->allowSuperfluousKeys()
            ->registerConstructor(
                \My\App\CustomerId::fromString(...),
            );
    }
}
```

This configurator can be registered within the `MapperBuilder` instance:

```php
$result = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(new \My\App\ApplicationMappingConfigurator())
    ->mapper()
    ->map(\My\App\User::class, [
        'id' => '604e4b36-5b76-4b1a-9e6c-02d5acb53a4d',
        'name' => 'John Doe',
        'extraField' => 'ignored because superfluous keys are allowed',
    ]);
```

## Composing multiple configurators

Multiple configurators can be combined to compose the final configuration.
Each configurator is applied in order, allowing layered and modular
configuration.

```php
namespace My\App;

use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Mapper\Configurator\MapperBuilderConfigurator;

final class FlexibleMappingConfigurator implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder
            ->allowScalarValueCasting()
            ->allowSuperfluousKeys();
    }
}

final class DomainConstructorsConfigurator implements MapperBuilderConfigurator
{
    public function configureMapperBuilder(MapperBuilder $builder): MapperBuilder
    {
        return $builder
            ->registerConstructor(
                \My\App\CustomerId::fromString(...),
                \My\App\Email::fromString(...),
            );
    }
}

$result = (new \CuyZ\Valinor\MapperBuilder())
    ->configureWith(
        new \My\App\FlexibleMappingConfigurator(),
        new \My\App\DomainConstructorsConfigurator(),
    )
    ->mapper()
    ->map(\My\App\User::class, $someData);
```

This approach keeps each configurator focused on a single concern, making them
easier to test and reuse independently.
