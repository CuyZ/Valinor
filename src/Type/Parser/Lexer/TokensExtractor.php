<?php

namespace CuyZ\Valinor\Type\Parser\Lexer;

use function array_map;
use function array_shift;
use function implode;
use function preg_split;

/** @internal */
final class TokensExtractor
{
    private const TOKEN_PATTERNS = [
        'Anonymous class' => '[a-zA-Z_\x7f-\xff][\\\\a-zA-Z0-9_\x7f-\xff]*+@anonymous\x00.*?\.php(?:0x?|:[0-9]++\$)[0-9a-fA-F]++',
        'Double colons' => '\:\:',
        'Triple dots' => '\.\.\.',
        'Dollar sign' => '\$',
        'Whitespace' => '\s',
        'Union' => '\|',
        'Intersection' => '&',
        'Opening bracket' => '\<',
        'Closing bracket' => '\>',
        'Opening square bracket' => '\[',
        'Closing square bracket' => '\]',
        'Opening curly bracket' => '\{',
        'Closing curly bracket' => '\}',
        'Colon' => '\:',
        'Question mark' => '\?',
        'Comma' => ',',
        'Single quote' => "'",
        'Double quote' => '"',
    ];

    /** @var list<string> */
    private array $symbols = [];

    public function __construct(string $string)
    {
        $pattern = '/(' . implode('|', self::TOKEN_PATTERNS) . ')' . '/';
        $tokens = preg_split($pattern, $string, flags: PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $quote = null;
        $text = null;

        while (($token = array_shift($tokens)) !== null) {
            if ($token === $quote) {
                if ($text !== null) {
                    $this->symbols[] = $text;
                }

                $this->symbols[] = $token;

                $text = null;
                $quote = null;
            } elseif ($quote !== null) {
                $text .= $token;
            } elseif ($token === '"' || $token === "'") {
                $quote = $token;

                $this->symbols[] = $token;
            } else {
                $this->symbols[] = $token;
            }
        }

        if ($text !== null) {
            $this->symbols[] = $text;
        }

        $this->symbols = array_map('trim', $this->symbols);
        $this->symbols = array_filter($this->symbols, static fn ($value) => $value !== '');
        $this->symbols = array_values($this->symbols);
    }

    /**
     * @return list<string>
     */
    public function all(): array
    {
        return $this->symbols;
    }
}
