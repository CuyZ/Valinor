<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DomainException;
use Throwable;

final class ExceptionFilteringTest extends IntegrationTestCase
{
    public function test_userland_exception_not_filtered_is_not_caught(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionCode(1657042062);
        $this->expectExceptionMessage('some error message');

        (new MapperBuilder())->mapper()->map(ClassThatThrowsExceptionIfInvalidValue::class, 'bar');
    }

    public function test_userland_exception_filtered_is_caught_and_added_to_mapping_errors(): void
    {
        try {
            (new MapperBuilder())
                ->filterExceptions(function (Throwable $exception): ErrorMessage {
                    if ($exception instanceof DomainException) {
                        return new FakeErrorMessage('some error message', 1657197780);
                    }

                    throw $exception;
                })
                ->mapper()
                ->map(ClassThatThrowsExceptionIfInvalidValue::class, 'bar');
        } catch (MappingError $exception) {
            $error = $exception->node()->messages()[0];

            self::assertSame('1657197780', $error->code());
            self::assertSame('some error message', (string)$error);
        }
    }
}

final class ClassThatThrowsExceptionIfInvalidValue
{
    public function __construct(string $value)
    {
        if ($value !== 'foo') {
            throw new DomainException('some error message', 1657042062);
        }
    }
}
