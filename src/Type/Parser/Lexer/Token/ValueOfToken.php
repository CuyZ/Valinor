<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use BackedEnum;
use CuyZ\Valinor\Type\Parser\Exception\Enum\NotBackedEnum;
use CuyZ\Valinor\Type\Parser\Exception\Magic\ClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Magic\OpeningBracketMissing;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\IsSingleton;

/** @internal */
final class ValueOfToken implements TraversingToken
{
    use IsSingleton;

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done() || !$stream->forward() instanceof OpeningBracketToken) {
            throw new OpeningBracketMissing($this->symbol());
        }

        $subType = $stream->read();

        if ($stream->done() || !$stream->forward() instanceof ClosingBracketToken) {
            throw new ClosingBracketMissing($this->symbol());
        }

        if ($subType instanceof UnionType && count($subType->types()) === 1) {
            $subType = $subType->types()[0];
        }

        if (! $subType instanceof EnumType) {
            throw new NotBackedEnum($subType->toString());
        }

        $list = [];
        foreach ($subType->cases() as $case) {
            if (! $case instanceof BackedEnum) {
                throw new NotBackedEnum($this->symbol());
            }
            if (is_string($case->value)) {
                $list[] =  StringValueType::from("'$case->value'");
            } else {
                $list[] = new IntegerValueType($case->value);
            }
        }

        return new UnionType(...$list);
    }

    public function symbol(): string
    {
        return 'value-of';
    }
}
