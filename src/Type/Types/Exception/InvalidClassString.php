<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types\Exception;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\String\StringFormatter;
use LogicException;

use function array_map;
use function count;
use function implode;

/** @internal */
final class InvalidClassString extends LogicException implements CastError
{
    private string $body;

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param ObjectType|UnionType|null $type
     */
    public function __construct(string $raw, ?Type $type)
    {
        if ($type instanceof ObjectType) {
            $expected = [$type];
        } elseif ($type instanceof UnionType) {
            $expected = $type->types();
        } else {
            $expected = [];
        }

        $expectedString = count($expected) !== 0
            ? '`' . implode('`, `', array_map(fn (Type $type) => $type->toString(), $expected)) . '`'
            : '';

        $this->parameters = [
            'value' => "`$raw`",
            'expected_class_strings' => $expectedString,
        ];

        if (count($expected) > 1) {
            $this->body = 'Invalid class string {value}, it must be one of {expected_class_strings}.';
        } elseif (count($expected) === 1) {
            $this->body = 'Invalid class string {value}, it must be a subtype of {expected_class_strings}.';
        } else {
            $this->body = 'Invalid class string {value}.';
        }

        parent::__construct(StringFormatter::for($this), 1608132562);
    }

    public function body(): string
    {
        return $this->body;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
