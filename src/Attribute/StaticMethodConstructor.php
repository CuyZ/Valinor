<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Attribute;

use Attribute;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;

/**
 * @api
 *
 * @deprecated This attribute should not be used anymore, the method
 *             `MapperBuilder::registerConstructor()` should be used instead.
 *
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class StaticMethodConstructor implements ObjectBuilderFactory
{
    private string $methodName;

    public function __construct(string $methodName)
    {
        $this->methodName = $methodName;
    }

    public function for(ClassDefinition $class, $source): ObjectBuilder
    {
        return new MethodObjectBuilder($class, $this->methodName);
    }
}
