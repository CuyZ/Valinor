<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\Parser\Exception\Magic\KeyOfClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Magic\KeyOfIncorrectSubType;
use CuyZ\Valinor\Type\Parser\Exception\Magic\KeyOfMissingSubType;
use CuyZ\Valinor\Type\Parser\Exception\Magic\KeyOfOpeningBracketMissing;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\IsSingleton;
use UnitEnum;

use function array_map;
use function array_values;
use function count;
use function str_contains;

/** @internal */
final class KeyOfToken implements TraversingToken
{
    use IsSingleton;

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done() || !$stream->forward() instanceof OpeningBracketToken) {
            throw new KeyOfOpeningBracketMissing();
        }

        if ($stream->done()) {
            throw new KeyOfMissingSubType();
        }

        $subType = $stream->read();

        if ($stream->done() || !$stream->forward() instanceof ClosingBracketToken) {
            throw new KeyOfClosingBracketMissing($subType);
        }

        if ($subType instanceof EnumType) {
            $keys = array_map(
                fn (UnitEnum $case) => StringValueType::from("'{$case->name}'"),
                array_values($subType->cases()),
            );

            if (count($keys) > 1) {
                return UnionType::from(...$keys);
            }

            return $keys[0];
        }

        if ($subType instanceof ShapedArrayType) {
            $keys = array_map(
                fn ($element) => $this->quoteStringKey($element->key()),
                array_values($subType->elements),
            );

            if (count($keys) > 1) {
                return UnionType::from(...$keys);
            }

            return $keys[0];
        }

        if ($subType instanceof CompositeTraversableType) {
            return $subType->keyType();
        }

        throw new KeyOfIncorrectSubType($subType);
    }

    private function quoteStringKey(StringValueType|IntegerValueType $key): StringValueType|IntegerValueType
    {
        if (!$key instanceof StringValueType) {
            return $key;
        }

        $value = $key->value();

        if (str_contains($value, "'")) {
            return StringValueType::from('"' . $value . '"');
        }

        return StringValueType::from("'$value'");
    }

    public function symbol(): string
    {
        return 'key-of';
    }
}
