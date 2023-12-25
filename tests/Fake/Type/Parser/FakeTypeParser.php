<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type\Parser;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

use function trim;

final class FakeTypeParser implements TypeParser
{
    /** @var array<string, Type> */
    private array $types = [];

    public function parse(string $raw): Type
    {
        $raw = trim($raw);

        if (isset($this->types[$raw])) {
            return $this->types[$raw];
        }

        $type = FakeType::from($raw);

        if ($type instanceof FakeType) {
            throw new class ("Type `$raw` not handled by `FakeTypeParser`.") extends RuntimeException implements InvalidType {};
        }

        return $type;
    }

    public function willReturn(string $raw, Type $type): void
    {
        $this->types[$raw] = $type;
    }
}
