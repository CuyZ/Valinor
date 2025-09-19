<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Iterable\ListClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ListMissingSubType;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;

/** @internal */
final class ListToken implements TraversingToken
{
    private static self $list;

    private static self $nonEmptyList;

    private function __construct(
        /** @var class-string<ListType|NonEmptyListType> */
        private string $listType,
        private string $symbol
    ) {}

    public static function list(): self
    {
        return self::$list ??= new self(ListType::class, 'list');
    }

    public static function nonEmptyList(): self
    {
        return self::$nonEmptyList ??= new self(NonEmptyListType::class, 'non-empty-list');
    }

    public function traverse(TokenStream $stream): Type
    {
        if (! $stream->done() && $stream->next() instanceof OpeningBracketToken) {
            $stream->forward();

            if ($stream->done()) {
                throw new ListMissingSubType($this->symbol);
            }

            $subType = $stream->read();

            $listType = new ($this->listType)($subType);

            if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
                throw new ListClosingBracketMissing($listType);
            }

            return $listType;
        }

        return ($this->listType)::native();
    }

    public function symbol(): string
    {
        return $this->symbol;
    }
}
