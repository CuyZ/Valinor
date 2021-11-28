<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type\Parser;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\BooleanType;
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

        if ($raw === 'bool') {
            return BooleanType::get();
        }

        if ($raw === 'string') {
            return NativeStringType::get();
        }

        if ($raw === 'array-key') {
            return ArrayKeyType::default();
        }

        throw new class ("Type `$raw` not handled by `FakeTypeParser`.") extends RuntimeException implements InvalidType { };
    }

    public function willReturn(string $raw, Type $type): void
    {
        $this->types[$raw] = $type;
    }
}
