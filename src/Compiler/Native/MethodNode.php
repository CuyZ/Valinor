<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler\Native;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;

use function array_map;
use function implode;

/** @internal */
final class MethodNode extends Node
{
    public function __construct(
        /** @var non-empty-string */
        private string $name,
        /** @var 'public'|'private' */
        private string $visibility = 'private',
        /** @var list<ParameterDeclarationNode> */
        private array $parameters = [],
        private ?string $returnType = null,
        /** @var list<Node> */
        private array $nodes = [],
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        $parameters = implode(', ', array_map(
            fn (ParameterDeclarationNode $parameter) => $compiler->sub()->compile($parameter)->code(),
            $this->parameters,
        ));

        $compiler = $compiler->write("$this->visibility function $this->name($parameters)");

        if ($this->name !== '__construct') {
            $compiler = $compiler->write(': ' . ($this->returnType ?? 'void'));
        }

        if ($this->nodes === []) {
            return $compiler->write(' {}');
        }

        $body = $compiler->sub()->indent()->compile(...$this->nodes)->code();

        return $compiler->write(PHP_EOL . '{' . PHP_EOL . $body . PHP_EOL . '}');
    }
}
