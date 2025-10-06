<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use LogicException;

/** @internal */
final class InterfaceHasBothConstructorAndInfer extends LogicException
{
    /**
     * @param interface-string $name
     */
    public function __construct(string $name)
    {
        parent::__construct("Interface `$name` is configured with at least one constructor but also has an infer configuration. Only one method can be used.");
    }
}
