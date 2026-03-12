<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Library;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Definition\AttributeDefinition;
use ReflectionClass;
use ReflectionProperty;

use function array_map;
use function CuyZ\Valinor\Compiler\{className, newClass, value};

/** @internal */
final class NewAttributeNode extends Node
{
    public function __construct(private AttributeDefinition $attribute) {}

    public function compile(Compiler $compiler): Compiler
    {
        if ($this->attribute->arguments !== null) {
            return $compiler->compile(
                newClass(
                    $this->attribute->class->name,
                    ...array_map(value(...), $this->attribute->arguments),
                ),
            );
        }

        // @phpstan-ignore match.unhandled (for now only those two cases can be handled here anyway)
        $node = match ($this->attribute->reflectionParts[0]) {
            'class' => newClass(
                ReflectionClass::class,
                className($this->attribute->reflectionParts[1])->asClassConstant()
            ),
            'property' => newClass(
                ReflectionProperty::class,
                className($this->attribute->reflectionParts[1])->asClassConstant(),
                value($this->attribute->reflectionParts[2])
            ),
        };

        return $compiler->compile(
            $node->wrap()
                ->callMethod('getAttributes')
                ->key(value($this->attribute->attributeIndex))
                ->callMethod('newInstance'),
        );
    }
}
