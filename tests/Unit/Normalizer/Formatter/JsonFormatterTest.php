<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Normalizer\Formatter;

use CuyZ\Valinor\Normalizer\Formatter\Exception\CannotFormatInvalidTypeToJson;
use CuyZ\Valinor\Normalizer\Formatter\JsonFormatter;
use PHPUnit\Framework\TestCase;

use function fopen;

final class JsonFormatterTest extends TestCase
{
    public function test_invalid_closure_type_given_to_formatter_throws_exception(): void
    {
        $this->expectException(CannotFormatInvalidTypeToJson::class);
        $this->expectExceptionMessage('Value of type `Closure` cannot be normalized to JSON.');
        $this->expectExceptionCode(1704749897);

        /** @var resource $resource */
        $resource = fopen('php://memory', 'r+');

        (new JsonFormatter($resource))->format(fn () => 42);
    }
}
