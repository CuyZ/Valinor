<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidArrayKey;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayColonTokenMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementTypeMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayEmptyElements;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\NonEmptyArrayType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;

/** @internal */
abstract class ShapedArrayToken implements TraversingToken {
    protected function shapedArrayType(TokenStream $stream): ShapedArrayType
    {
        $stream->forward();

        $elements = [];
        $index = 0;

        while (! $stream->done()) {
            if ($stream->next() instanceof ClosingCurlyBracketToken) {
                $stream->forward();
                break;
            }

            if (! empty($elements) && ! $stream->forward() instanceof CommaToken) {
                throw new ShapedArrayCommaMissing($elements);
            }

            if ($stream->next() instanceof ClosingCurlyBracketToken) {
                $stream->forward();
                break;
            }

            $optional = false;

            if ($stream->next() instanceof UnknownSymbolToken) {
                $type = new StringValueType($stream->forward()->symbol());
            } else {
                $type = $stream->read();
            }

            if ($stream->done()) {
                $elements[] = new ShapedArrayElement(new StringValueType((string)$index), $type);

                throw new ShapedArrayClosingBracketMissing($elements);
            }

            if ($stream->next() instanceof NullableToken) {
                $stream->forward();
                $optional = true;

                if ($stream->done()) {
                    throw new ShapedArrayColonTokenMissing($elements, $type);
                }
            }

            if ($stream->next() instanceof ColonToken) {
                $stream->forward();

                $key = $type;
                $type = null;

                if (! $key instanceof StringValueType && ! $key instanceof IntegerValueType) {
                    $key = new StringValueType($key->toString());
                }

                if ($key instanceof IntegerValueType) {
                    $index++;
                }
            } else {
                if ($optional) {
                    throw new ShapedArrayColonTokenMissing($elements, $type);
                }

                $key = new IntegerValueType($index++);
            }

            if (! $type) {
                if ($stream->done()) {
                    throw new ShapedArrayElementTypeMissing($elements, $key, $optional);
                }

                $type = $stream->read();
            }

            $elements[] = new ShapedArrayElement($key, $type, $optional);

            if ($stream->done()) {
                throw new ShapedArrayClosingBracketMissing($elements);
            }
        }

        if (empty($elements)) {
            throw new ShapedArrayEmptyElements();
        }

        return new ShapedArrayType(...$elements);
    }
}