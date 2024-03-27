<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Exception\InvalidMappingTypeSignature;
use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringMapping;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\TreeNode;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\TypeParser;

/** @internal */
final class TypeTreeMapper implements TreeMapper
{
    public function __construct(
        private TypeParser $typeParser,
        private RootNodeBuilder $nodeBuilder
    ) {}

    /** @pure */
    public function map(string $signature, mixed $source): mixed
    {
        $node = $this->node($signature, $source);

        if (! $node->isValid()) {
            throw new TypeTreeMapperError($node->node());
        }

        return $node->value();
    }

    private function node(string $signature, mixed $source): TreeNode
    {
        try {
            $type = $this->typeParser->parse($signature);
        } catch (InvalidType $exception) {
            throw new InvalidMappingTypeSignature($signature, $exception);
        }

        $shell = Shell::root($type, $source);

        try {
            return $this->nodeBuilder->build($shell);
        } catch (UnresolvableShellType $exception) {
            throw new TypeErrorDuringMapping($type, $exception);
        }
    }
}
