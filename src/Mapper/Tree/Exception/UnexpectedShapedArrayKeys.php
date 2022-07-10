<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Utility\String\StringFormatter;
use RuntimeException;

use function array_map;
use function implode;

/** @internal */
final class UnexpectedShapedArrayKeys extends RuntimeException implements TranslatableMessage
{
    private string $body = 'Unexpected key(s) {keys}, expected {expected_keys}.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param array<string> $keys
     * @param array<ShapedArrayElement> $elements
     */
    public function __construct(array $keys, array $elements)
    {
        $expected = array_map(fn (ShapedArrayElement $element) => $element->key()->toString(), $elements);

        $this->parameters = [
            'keys' => '`' . implode('`, `', $keys) . '`',
            'expected_keys' => '`' . implode('`, `', $expected) . '`',
        ];

        parent::__construct(StringFormatter::for($this), 1655117782);
    }

    public function body(): string
    {
        return $this->body;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }
}
