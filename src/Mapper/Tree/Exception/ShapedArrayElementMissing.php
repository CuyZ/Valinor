<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\TranslatableMessage;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Utility\String\StringFormatter;
use CuyZ\Valinor\Utility\TypeHelper;
use RuntimeException;

/** @internal */
final class ShapedArrayElementMissing extends RuntimeException implements TranslatableMessage
{
    private string $body = 'Cannot be empty and must be filled with a value matching type {expected_type}.';

    /** @var array<string, string> */
    private array $parameters;

    public function __construct(ShapedArrayElement $element)
    {
        $this->parameters = [
            'expected_type' => TypeHelper::dump($element->type()),
        ];

        parent::__construct(StringFormatter::for($this), 1631613641);
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
