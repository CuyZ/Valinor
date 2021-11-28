<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Visitor;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Resolver\Union\UnionNarrower;
use CuyZ\Valinor\Type\Types\UnionType;

final class UnionShellVisitor implements ShellVisitor
{
    private UnionNarrower $unionNarrower;

    public function __construct(UnionNarrower $unionNarrower)
    {
        $this->unionNarrower = $unionNarrower;
    }

    public function visit(Shell $shell): Shell
    {
        $type = $shell->type();
        $value = $shell->value();

        if (! $type instanceof UnionType) {
            return $shell;
        }

        $narrowedType = $this->unionNarrower->narrow($type, $value);

        return $shell->withType($narrowedType);
    }
}
