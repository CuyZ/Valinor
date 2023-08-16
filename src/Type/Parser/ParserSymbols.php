<?php

namespace CuyZ\Valinor\Type\Parser;

/** @internal */
final class ParserSymbols
{
    private const OPERATORS = [' ', '|', '&', '<', '>', '[', ']', '{', '}', ':', '?', ',', "'", '"'];

    private ?string $current = null;

    /** @var list<string> */
    private array $symbols = [];

    public function __construct(string $string)
    {
        $quote = null;

        foreach (str_split($string) as $char) {
            if ($quote !== null) {
                if ($char === $quote) {
                    $this->addCurrent();
                    $this->symbols[] = $char;

                    $quote = null;
                } else {
                    $this->current .= $char;
                }
            } elseif (in_array($char, self::OPERATORS, true)) {
                $this->addCurrent();
                $this->symbols[] = $char;

                if ($char === '"' || $char === "'") {
                    $quote = $char;
                }
            } else {
                $this->current .= $char;
            }
        }

        $this->addCurrent();

        $this->symbols = array_map('trim', $this->symbols);
        $this->symbols = array_filter($this->symbols, static fn ($value) => $value !== '');

        $this->mergeDoubleColons();
        $this->detectAnonymousClass();
    }

    /**
     * @return list<string>
     */
    public function all(): array
    {
        return $this->symbols;
    }

    private function addCurrent(): void
    {
        if ($this->current !== null) {
            $this->symbols[] = $this->current;
            $this->current = null;
        }
    }

    private function mergeDoubleColons(): void
    {
        foreach ($this->symbols as $key => $symbol) {
            /** @infection-ignore-all should not happen so it is not tested */
            if ($key === 0) {
                continue;
            }

            if ($symbol === ':' && $this->symbols[$key - 1] === ':') {
                $this->symbols[$key - 1] = '::';
                unset($this->symbols[$key]);
            }
        }
    }

    private function detectAnonymousClass(): void
    {
        foreach ($this->symbols as $key => $symbol) {
            if (! str_starts_with($symbol, "class@anonymous\0")) {
                continue;
            }

            $this->symbols[$key] = $symbol . $this->symbols[$key + 1] . $this->symbols[$key + 2];

            array_splice($this->symbols, $key + 1, 2);
        }
    }
}
