<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\TypeHelper;

/** @internal */
final class UnresolvableTypeFinderParser implements TypeParser
{
    public function __construct(
        private TypeParser $delegate,
    ) {}

    /**
     * After the type has been parsed by the delegate, we recursively check if
     * any subtype is an unresolvable type. If so, we return this as the root
     * type.
     *
     * This prevents partially valid types from being returned.
     */
    public function parse(string $raw): Type
    {
        $type = $this->delegate->parse($raw);

        foreach (TypeHelper::traverseRecursively($type) as $subType) {
            if ($subType instanceof UnresolvableType) {
                return new UnresolvableType($raw, $subType->message());
            }
        }

        return $type;
    }
}
