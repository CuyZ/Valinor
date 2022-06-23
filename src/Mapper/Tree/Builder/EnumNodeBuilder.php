<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use BackedEnum;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidEnumValue;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\EnumType;
use Stringable;
use UnitEnum;

use function assert;
use function filter_var;
use function is_bool;
use function is_numeric;
use function is_string;

/** @internal */
final class EnumNodeBuilder implements NodeBuilder
{
    private bool $flexible;

    public function __construct(bool $flexible)
    {
        $this->flexible = $flexible;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof EnumType);

        foreach ($type->className()::cases() as $case) {
            if ($this->valueMatchesEnumCase($value, $case)) {
                return Node::leaf($shell, $case);
            }
        }

        throw new InvalidEnumValue($type->className(), $value);
    }

    /**
     * @param mixed $value
     */
    private function valueMatchesEnumCase($value, UnitEnum $case): bool
    {
        if (! $case instanceof BackedEnum) {
            return $value === $case->name;
        }

        if (! $this->flexible) {
            return $value === $case->value;
        }

        if (is_string($case->value)) {
            if (! is_string($value) && ! is_numeric($value) && ! $value instanceof Stringable) {
                return false;
            }

            return (string)$value === $case->value;
        }

        if (is_bool($value) || filter_var($value, FILTER_VALIDATE_INT) === false) {
            return false;
        }

        return (int)$value === $case->value; // @phpstan-ignore-line
    }
}
