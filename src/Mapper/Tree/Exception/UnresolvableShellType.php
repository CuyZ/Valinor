<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Exception\MappingLogicalException;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use LogicException;

/** @internal */
final class UnresolvableShellType extends LogicException implements MappingLogicalException
{
    public function __construct(UnresolvableType $type)
    {
        parent::__construct($type->message());
    }
}
