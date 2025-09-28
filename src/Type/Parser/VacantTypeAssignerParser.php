<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser;

use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;

/** @internal */
final class VacantTypeAssignerParser implements TypeParser
{
    public function __construct(
        private TypeParser $delegate,
        /** @var array<non-empty-string, Type> */
        private array $vacantTypes,
    ) {
        foreach ($this->vacantTypes as $key => $vacantType) {
            $this->vacantTypes[$key] = self::assignVacantTypes($vacantType, $this->vacantTypes);
        }
    }

    public function parse(string $raw): Type
    {
        $type = $this->delegate->parse($raw);

        if ($this->vacantTypes !== []) {
            try {
                $type = self::assignVacantTypes($type, $this->vacantTypes);
            } catch (InvalidType $exception) {
                return new UnresolvableType($raw, $exception->getMessage());
            }
        }

        return $type;
    }

    /**
     * @param array<non-empty-string, Type> $vacantTypes
     */
    private static function assignVacantTypes(Type $type, array $vacantTypes): Type
    {
        if ($type instanceof UnresolvableType && isset($vacantTypes[$type->toString()])) {
            return $vacantTypes[$type->toString()];
        }

        if ($type instanceof CompositeType) {
            return $type->replace(
                static fn (Type $subType) => self::assignVacantTypes($subType, $vacantTypes),
            );
        }

        return $type;
    }

}
