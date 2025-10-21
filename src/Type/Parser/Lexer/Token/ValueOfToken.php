<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use BackedEnum;
use CuyZ\Valinor\Type\Parser\Exception\Magic\ValueOfClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Magic\ValueOfIncorrectSubType;
use CuyZ\Valinor\Type\Parser\Exception\Magic\ValueOfMissingSubType;
use CuyZ\Valinor\Type\Parser\Exception\Magic\ValueOfOpeningBracketMissing;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\Factory\ValueTypeFactory;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\IsSingleton;

use function array_map;
use function array_values;
use function count;
use function is_a;

/** @internal */
final class ValueOfToken implements TraversingToken
{
    use IsSingleton;

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done() || !$stream->forward() instanceof OpeningBracketToken) {
            throw new ValueOfOpeningBracketMissing();
        }

        if ($stream->done()) {
            throw new ValueOfMissingSubType();
        }

        $subType = $stream->read();

        if ($stream->done() || !$stream->forward() instanceof ClosingBracketToken) {
            throw new ValueOfClosingBracketMissing($subType);
        }

        if (! $subType instanceof EnumType) {
            throw new ValueOfIncorrectSubType($subType);
        }

        if (! is_a($subType->className(), BackedEnum::class, true)) {
            throw new ValueOfIncorrectSubType($subType);
        }

        $cases = array_map(
            // @phpstan-ignore-next-line / We know it's a BackedEnum
            fn (BackedEnum $case) => ValueTypeFactory::from($case->value),
            array_values($subType->cases()),
        );

        if (count($cases) > 1) {
            return UnionType::from(...$cases);
        }

        return $cases[0];
    }

    public function symbol(): string
    {
        return 'value-of';
    }
}
