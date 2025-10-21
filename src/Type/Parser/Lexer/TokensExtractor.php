<?php

namespace CuyZ\Valinor\Type\Parser\Lexer;

use function array_filter;
use function array_map;
use function array_values;
use function implode;
use function preg_split;
use function trim;

/** @internal */
final class TokensExtractor
{
    private const TOKEN_PATTERNS = [
        'Anonymous class' => '[a-zA-Z_\x7f-\xff][\\\\a-zA-Z0-9_\x7f-\xff]*+@anonymous\x00.*?\.php(?:0x?|:[0-9]++\$)[0-9a-fA-F]++',
        'Simple quoted string' => '\'(?:\\\\[^\\r\\n]|[^\'\\r\\n])*+\'?',
        'Double quoted string' => '"(?:\\\\[^\\r\\n]|[^"\\r\\n])*+"?',
        'Double colons' => '\:\:',
        'Triple dots' => '\.\.\.',
        'Dollar sign' => '\$',
        'Whitespace' => '\s+',
        'Union' => '\|',
        'Intersection' => '&',
        'Opening parenthesis' => '\(',
        'Closing parenthesis' => '\)',
        'Opening bracket' => '\<',
        'Closing bracket' => '\>',
        'Opening square bracket' => '\[',
        'Closing square bracket' => '\]',
        'Opening curly bracket' => '\{',
        'Closing curly bracket' => '\}',
        'Colon' => '\:',
        'Equal' => '=',
        'Question mark' => '\?',
        'Comma' => ',',
        'Single quote' => "'",
        'Double quote' => '"',
    ];

    /** @var non-empty-list<string> */
    private array $symbols;

    public function __construct(string $raw)
    {
        $pattern = '/(' . implode('|', self::TOKEN_PATTERNS) . ')' . '/';

        // @phpstan-ignore-next-line / We know the pattern is valid and the returned array contains at least one string
        $this->symbols = preg_split($pattern, $raw, flags: PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @return non-empty-list<string>
     */
    public function all(): array
    {
        return $this->symbols;
    }

    /**
     * @return list<non-empty-string>
     */
    public function filtered(): array
    {
        // PHP8.5 use pipes
        return array_values(array_filter(
            array_map(trim(...), $this->symbols),
            static fn ($value) => $value !== '',
        ));
    }
}
