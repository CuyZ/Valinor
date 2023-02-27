<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type;

use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use stdClass;

use function in_array;

final class FakeType implements Type
{
    private static int $counter = 0;

    private string $name;

    private Type $matching;

    /** @var mixed[] */
    private array $accepting;

    private bool $permissive = false;

    public function __construct(string $name = null)
    {
        $this->name = $name ?? 'FakeType' . self::$counter++;
    }

    public static function from(string $raw): Type
    {
        if ($raw === 'string') {
            return NativeStringType::get();
        }

        if ($raw === 'bool') {
            return NativeBooleanType::get();
        }

        if ($raw === 'array-key') {
            return ArrayKeyType::default();
        }

        if ($raw === stdClass::class) {
            return new NativeClassType(stdClass::class);
        }

        if ($raw === StringableObject::class) {
            return new NativeClassType(StringableObject::class);
        }

        return new self();
    }

    public static function permissive(): self
    {
        $instance = new self();
        $instance->permissive = true;

        return $instance;
    }

    public static function accepting(mixed ...$values): self
    {
        $instance = new self();
        $instance->accepting = $values;

        return $instance;
    }

    public static function matching(Type $other): self
    {
        $instance = new self();
        $instance->matching = $other;

        return $instance;
    }

    public function accepts(mixed $value): bool
    {
        return $this->permissive
            || (isset($this->accepting) && in_array($value, $this->accepting, true));
    }

    public function matches(Type $other): bool
    {
        return $other === $this
            || $other instanceof MixedType
            || $other === ($this->matching ?? null);
    }

    public function toString(): string
    {
        return $this->name;
    }
}
