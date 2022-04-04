<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\TypeHelper;
use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

use function implode;

/** @api */
final class CannotResolveTypeFromUnion extends RuntimeException implements Message
{
    /**
     * @param mixed $value
     */
    public function __construct(UnionType $unionType, $value)
    {
        $value = ValueDumper::dump($value);
        $message = TypeHelper::containsObject($unionType)
            ? "Value $value is not accepted."
            : "Value $value does not match any of `" . implode('`, `', $unionType->types()) . "`.";

        parent::__construct($message, 1607027306);
    }
}
