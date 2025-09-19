<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Library;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Definition\AttributeDefinition;
use ReflectionClass;
use ReflectionProperty;

use function array_map;

/** @internal */
final class NewAttributeNode extends Node
{
    public function __construct(private AttributeDefinition $attribute) {}

    public function compile(Compiler $compiler): Compiler
    {
        if ($this->attribute->arguments !== null) {
            return $compiler->compile(
                Node::newClass(
                    $this->attribute->class->name,
                    ...array_map(Node::value(...), $this->attribute->arguments),
                ),
            );
        }

        // @phpstan-ignore match.unhandled (for now only those two cases can be handled here anyway)
        $node = match ($this->attribute->reflectionParts[0]) {
            'class' => Node::newClass(ReflectionClass::class, Node::className($this->attribute->reflectionParts[1])),
            'property' => Node::newClass(ReflectionProperty::class, Node::className($this->attribute->reflectionParts[1]), Node::value($this->attribute->reflectionParts[2])),
        };

        return $compiler->compile(
            $node->wrap()
                ->callMethod('getAttributes')
                ->key(Node::value($this->attribute->attributeIndex))
                ->callMethod('newInstance'),
        );
    }
}
