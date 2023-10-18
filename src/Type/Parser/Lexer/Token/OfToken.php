<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Magic\ClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Magic\NonArrayOf;
use CuyZ\Valinor\Type\Parser\Exception\Magic\OpeningBracketMissing;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnionType;

/** @internal */
final class OfToken implements TraversingToken
{
    private static self $keyOf;

    private static self $valueOf;

    private function __construct(
        private bool $key
    ) {}

    public static function keyOf(): self
    {
        return self::$keyOf ??= new self(true);
    }

    public static function valueOf(): self
    {
        return self::$valueOf ??= new self(false);
    }

    public function traverse(TokenStream $stream): Type
    {
        if ($stream->done() || ! $stream->forward() instanceof OpeningBracketToken) {
            throw new OpeningBracketMissing($this->symbol());
        }

        $subType = $stream->read();

        if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
            throw new ClosingBracketMissing($this->symbol());
        }

        if ($subType instanceof UnionType && count($subType->types()) === 1) {
            $subType = $subType->types()[0];
        }

        if ($subType instanceof ShapedArrayType) {
            $list = [];
            foreach ($subType->elements() as $element) {
                if ($this->key) {
                    $list []= $element->key();
                } else {
                    $list []= $element->type();
                }
            }
            $subType = new UnionType(...$list);
        } elseif ($subType instanceof ArrayType) {
            if ($this->key) {
                $subType = $subType->keyType();
            } else {
                $subType = $subType->subType();
            }
        } else {
            throw new NonArrayOf($this->symbol(), $subType);
        }

        return $subType;
    }

    public function symbol(): string
    {
        return $this->key ? 'key-of' : 'value-of';
    }
}
