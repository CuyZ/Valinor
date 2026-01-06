<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\Type;

/** @internal */
final readonly class Generics
{
    public function __construct(
        /** @var array<non-empty-string, Type> */
        public array $items = [],
    ) {}

    public function with(GenericType $generic, Type $type): self
    {
        $name = $generic->symbol;

        if (isset($this->items[$name])) {
            $other = $this->items[$name];

            if ($other->matches($type)) {
                $type = $other;
            } elseif (! $type->matches($other)) {
                $type = UnionType::from($other, $type);
            }
        }

        return new self([...$this->items, ...[$name => $type]]);
    }
}
