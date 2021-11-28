<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Iterable\ListClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\NonEmptyListType;

final class ListToken implements TraversingToken
{
    /** @var class-string<ListType|NonEmptyListType> */
    private string $listType;

    private static self $list;

    private static self $nonEmptyList;

    /**
     * @param class-string<ListType|NonEmptyListType> $listType
     */
    private function __construct(string $listType)
    {
        $this->listType = $listType;
    }

    public static function list(): self
    {
        return self::$list ??= new self(ListType::class);
    }

    public static function nonEmptyList(): self
    {
        return self::$nonEmptyList ??= new self(NonEmptyListType::class);
    }

    public function traverse(TokenStream $stream): Type
    {
        if (! $stream->done() && $stream->next() instanceof OpeningBracketToken) {
            $stream->forward();

            $subType = $stream->read();

            // @PHP8.0 use `new ($this->listType)(...)`
            $listClass = $this->listType;
            $listType = new $listClass($subType);

            if ($stream->done() || ! $stream->forward() instanceof ClosingBracketToken) {
                throw new ListClosingBracketMissing($listType);
            }

            return $listType;
        }

        return ($this->listType)::native();
    }
}
