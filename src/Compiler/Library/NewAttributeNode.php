<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Library;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Definition\AttributeDefinition;

use function array_map;
use function serialize;

/** @internal */
final class NewAttributeNode extends Node
{
    public function __construct(private AttributeDefinition $attribute) {}

    public function compile(Compiler $compiler): Compiler
    {
        $argumentNodes = $this->argumentNode($this->attribute->arguments);

        return $compiler->compile(
            Node::newClass(
                $this->attribute->class->name,
                ...$argumentNodes,
            ),
        );
    }

    /**
     * @param array<mixed> $arguments
     * @return list<Node>
     */
    private function argumentNode(array $arguments): array
    {
        return array_map(function (mixed $argument) {
            if (is_object($argument)) {
                return Node::functionCall(
                    name: 'unserialize',
                    arguments: [Node::value(serialize($argument))],
                );
            }

            if (is_array($argument)) {
                return Node::array($this->argumentNode($argument));
            }

            /** @var scalar $argument */
            return Node::value($argument);
        }, $arguments);
    }
}
