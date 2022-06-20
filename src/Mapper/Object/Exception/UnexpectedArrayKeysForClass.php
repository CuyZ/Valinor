<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use CuyZ\Valinor\Utility\String\StringFormatter;
use RuntimeException;

use function array_filter;
use function array_keys;

/** @api */
final class UnexpectedArrayKeysForClass extends RuntimeException implements TranslatableMessage
{
    private string $body = 'Unexpected key(s) {keys}, expected {expected_keys}.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param array<mixed> $value
     */
    public function __construct(array $value, Arguments $arguments)
    {
        $keys = array_filter(array_keys($value), fn ($key) => ! $arguments->has((string)$key));
        $expected = array_map(fn (Argument $argument) => $argument->name(), [...$arguments]);

        $this->parameters = [
            'keys' => '`' . implode('`, `', $keys) . '`',
            'expected_keys' => '`' . implode('`, `', $expected) . '`',
        ];

        parent::__construct(StringFormatter::for($this), 1655149208);
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
