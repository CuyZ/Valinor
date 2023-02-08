<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use BackedEnum;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\Parser\Exception\Enum\EnumCaseNotFound;
use CuyZ\Valinor\Type\Parser\Lexer\Token\CaseFinder;
use CuyZ\Valinor\Type\Type;
use UnitEnum;

use function array_keys;
use function in_array;

/** @internal */
final class EnumType implements ClassType
{
    /** @var class-string<UnitEnum> */
    private string $enumName;

    private string $pattern;

    /** @var array<UnitEnum> */
    private array $cases;

    /**
     * @param class-string<UnitEnum> $enumName
     * @param array<UnitEnum> $cases
     */
    public function __construct(string $enumName, string $pattern, array $cases)
    {
        $this->enumName = $enumName;
        $this->pattern = $pattern;

        if (empty($cases)) {
            throw new EnumCaseNotFound($this->enumName, $pattern);
        }

        foreach ($cases as $case) {
            $this->cases[$case instanceof BackedEnum ? $case->value : $case->name] = $case;
        }
    }

    /**
     * @param class-string<UnitEnum> $enumName
     */
    public static function native(string $enumName): self
    {
        return new self($enumName, '', $enumName::cases());
    }

    /**
     * @param class-string<UnitEnum> $enumName
     */
    public static function fromPattern(string $enumName, string $pattern): self
    {
        $namedCases = [];

        foreach ($enumName::cases() as $case) {
            /** @var UnitEnum $case */
            $namedCases[$case->name] = $case;
        }

        $cases = (new CaseFinder($namedCases))->matching(explode('*', $pattern));

        return new self($enumName, $pattern, $cases);
    }

    /**
     * @return class-string<UnitEnum>
     */
    public function className(): string
    {
        return $this->enumName;
    }

    /**
     * @return array<UnitEnum>
     */
    public function cases(): array
    {
        return $this->cases;
    }

    public function pattern(): string
    {
        return $this->pattern;
    }

    public function accepts(mixed $value): bool
    {
        return $value instanceof $this->enumName;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof CombiningType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof self) {
            if ($other->enumName !== $this->enumName) {
                return false;
            }

            foreach ($this->cases as $case) {
                if (! in_array($case, $other->cases, true)) {
                    return false;
                }
            }

            return true;
        }

        return $other instanceof UndefinedObjectType
            || $other instanceof MixedType;
    }

    public function readableSignature(): string
    {
        return implode('|', array_keys($this->cases));
    }

    public function toString(): string
    {
        return $this->pattern === ''
            ? $this->enumName
            : "$this->enumName::$this->pattern";
    }
}
