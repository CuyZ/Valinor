<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Exception\InvalidMappingTypeSignature;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\TypeParser;

final class TreeMapperContainer implements TreeMapper
{
    private TypeParser $typeParser;

    private RootNodeBuilder $nodeBuilder;

    public function __construct(TypeParser $typeParser, RootNodeBuilder $nodeBuilder)
    {
        $this->typeParser = $typeParser;
        $this->nodeBuilder = $nodeBuilder;
    }

    public function map(string $signature, $source)
    {
        $node = $this->node($signature, $source);

        if (! $node->isValid()) {
            throw new MappingError($node);
        }

        return $node->value();
    }

    /**
     * @param mixed $source
     */
    private function node(string $signature, $source): Node
    {
        try {
            $type = $this->typeParser->parse($signature);
        } catch (InvalidType $exception) {
            throw new InvalidMappingTypeSignature($signature, $exception);
        }

        $shell = Shell::root($type, $source);

        return $this->nodeBuilder->build($shell);
    }
}
