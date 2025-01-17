<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
use CuyZ\Valinor\Utility\String\StringFormatter;
use RuntimeException;

use function array_filter;
use function array_keys;
use function implode;
use function in_array;

/** @internal */
final class UnexpectedKeysInSource extends RuntimeException implements ErrorMessage, HasParameters
{
    private string $body = 'Unexpected key(s) {keys}, expected {expected_keys}.';

    /** @var array<string, string> */
    private array $parameters;

    /**
     * @param array<mixed> $value
     * @param non-empty-list<int|string> $children
     */
    public function __construct(array $value, array $children)
    {
        $superfluous = array_filter(
            array_keys($value),
            fn (string $key) => ! in_array($key, $children, true)
        );

        $this->parameters = [
            'keys' => '`' . implode('`, `', $superfluous) . '`',
            'expected_keys' => '`' . implode('`, `', $children) . '`',
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
