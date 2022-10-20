<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Enum\EnumCaseNotFound;
use CuyZ\Valinor\Type\Parser\Exception\Enum\MissingEnumCase;
use CuyZ\Valinor\Type\Parser\Exception\Enum\MissingEnumColon;
use CuyZ\Valinor\Type\Parser\Exception\Enum\MissingSpecificEnumCase;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Factory\ValueTypeFactory;
use CuyZ\Valinor\Type\Types\NativeEnumType;
use CuyZ\Valinor\Type\Types\UnionType;
use UnitEnum;

use function array_map;
use function count;
use function explode;
use function preg_match;
use function reset;

/** @internal */
final class EnumNameToken implements TraversingToken
{
    public function __construct(
        /** @var class-string<UnitEnum> */
        private string $enumName
    ) {
    }

    public function traverse(TokenStream $stream): Type
    {
        $cases = $this->findCases($stream);

        if ($cases) {
            return $cases;
        }

        return new NativeEnumType($this->enumName);
    }

    private function findCases(TokenStream $stream): ?Type
    {
        if ($stream->done() || ! $stream->next() instanceof ColonToken) {
            return null;
        }

        $case = $stream->forward();
        $missingColon = true;

        if (! $stream->done()) {
            $case = $stream->forward();

            $missingColon = ! $case instanceof ColonToken;
        }

        if (! $missingColon) {
            if ($stream->done()) {
                throw new MissingEnumCase($this->enumName);
            }

            $case = $stream->forward();
        }

        $symbol = $case->symbol();

        if ($symbol === '*') {
            throw new MissingSpecificEnumCase($this->enumName);
        }

        if ($missingColon) {
            throw new MissingEnumColon($this->enumName, $symbol);
        }

        $cases = [];

        if (! preg_match('/\*\s*\*/', $symbol)) {
            $finder = new CaseFinder($this->cases());
            $cases = $finder->matching(explode('*', $symbol));
        }

        if (empty($cases)) {
            throw new EnumCaseNotFound($this->enumName, $symbol);
        }

        $cases = array_map(static fn ($value) => ValueTypeFactory::from($value), $cases);

        if (count($cases) > 1) {
            return new UnionType(...$cases);
        }

        return reset($cases);
    }

    /**
     * @return array<string, UnitEnum>
     */
    private function cases(): array
    {
        $cases = [];

        foreach ($this->enumName::cases() as $case) {
            /** @var UnitEnum $case */
            $cases[$case->name] = $case;
        }

        return $cases;
    }

    public function symbol(): string
    {
        return $this->enumName;
    }
}
