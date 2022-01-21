<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Attribute;

use Attribute;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @api
 *
 * @deprecated This attribute is not useful anymore, automatic named constructor
 *             resolution is now provided by the library out of the box.
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
