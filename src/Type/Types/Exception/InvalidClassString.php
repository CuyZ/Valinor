<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnionType;
use LogicException;

use function count;
use function implode;

/** @api */
final class InvalidClassString extends LogicException implements CastError
{
    /**
     * @param ObjectType|UnionType|null $type
     */
    public function __construct(string $raw, ?Type $type)
    {
        $types = [];

        if ($type instanceof ObjectType) {
            $types = [$type];
        } elseif ($type instanceof UnionType) {
            $types = $type->types();
        }

        $message = "Invalid class string `$raw`.";

        if (count($types) > 1) {
            $message = "Invalid class string `$raw`, it must be one of `" . implode('`, `', $types) . "`.";
        } elseif (count($types) === 1) {
            $message = "Invalid class string `$raw`, it must be a subtype of `$type`.";
        }

        parent::__construct($message, 1608132562);
    }
}
