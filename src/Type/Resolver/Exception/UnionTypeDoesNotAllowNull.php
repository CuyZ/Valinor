<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use RuntimeException;

/** @api */
final class UnionTypeDoesNotAllowNull extends RuntimeException implements TranslatableMessage
{
    private string $body;

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(UnionType $unionType)
    {
        $this->parameters = [
            'expected_type' => TypeHelper::dump($unionType),
        ];

        $this->body = TypeHelper::containsObject($unionType)
            ? 'Cannot be empty.'
            : 'Cannot be empty and must be filled with a value matching type {expected_type}.';

        parent::__construct(StringFormatter::for($this), 1618742357);
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
