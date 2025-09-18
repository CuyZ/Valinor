<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeInvalidMaxValue;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeInvalidMinValue;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeMissingClosingBracket;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeMissingComma;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeMissingMaxValue;
use CuyZ\Valinor\Type\Parser\Exception\Scalar\IntegerRangeMissingMinValue;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\IntegerRangeType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Utility\IsSingleton;

/** @internal */
final class IntegerToken implements TraversingToken
{
    use IsSingleton;

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done() || ! $stream->next() instanceof OpeningBracketToken) {
            return NativeIntegerType::get();
        }

        $stream->forward();

        if ($stream->done()) {
            throw new IntegerRangeMissingMinValue();
        }

        if ($stream->next()->symbol() === 'min') {
            $min = new IntegerValueType(PHP_INT_MIN);
            $stream->forward();
        } else {
            $min = $stream->read();
        }

        if (! $min instanceof IntegerValueType) {
            throw new IntegerRangeInvalidMinValue($min);
        }

        if ($stream->done() || ! $stream->forward() instanceof CommaToken) {
            throw new IntegerRangeMissingComma($min);
        }

        if ($stream->done()) {
            throw new IntegerRangeMissingMaxValue($min);
        }

        if ($stream->next()->symbol() === 'max') {
            $max = new IntegerValueType(PHP_INT_MAX);
            $stream->forward();
        } else {
            $max = $stream->read();
        }

        if (! $max instanceof IntegerValueType) {
            throw new IntegerRangeInvalidMaxValue($min, $max);
        }

        if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
            throw new IntegerRangeMissingClosingBracket($min, $max);
        }

        return IntegerRangeType::from($min->value(), $max->value());
    }

    public function symbol(): string
    {
        return 'int';
    }
}
