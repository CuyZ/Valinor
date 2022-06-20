<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\FilledArguments;
use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use CuyZ\Valinor\Utility\String\StringFormatter;
use RuntimeException;

use function array_map;
use function implode;

/** @api */
final class UnexpectedArrayKeysForClass extends RuntimeException implements TranslatableMessage
{
    private string $body = 'Unexpected key(s) {keys}, expected {expected_keys}.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param array<string> $keys
     */
    public function __construct(array $keys, FilledArguments $arguments)
    {
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
