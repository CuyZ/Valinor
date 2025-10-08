<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use RuntimeException;

use function assert;

/** @internal */
final class ConverterHasInvalidReturnType extends RuntimeException
{
    public function __construct(FunctionDefinition $function)
    {
        assert($function->returnType instanceof UnresolvableType);

        parent::__construct($function->returnType->message());
    }
}
