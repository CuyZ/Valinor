<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Type\Types\UnresolvableType;
use RuntimeException;

final class UnresolvableShellType extends RuntimeException
{
    public function __construct(UnresolvableType $type)
    {
        parent::__construct(
            $type->getMessage(),
            1630943848,
            $type
        );
    }
}
