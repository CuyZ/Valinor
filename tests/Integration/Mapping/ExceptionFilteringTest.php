<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use DateTimeInterface;
use DomainException;
use Throwable;

final class ExceptionFilteringTest extends IntegrationTestCase
{
    public function test_userland_exception_not_filtered_is_not_caught(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('some error message');

        $this->mapperBuilder()->mapper()->map(ClassThatThrowsExceptionIfInvalidValue::class, 'bar');
    }

    public function test_userland_exception_filtered_is_caught_and_added_to_mapping_errors(): void
    {
        try {
            $this->mapperBuilder()
                ->filterExceptions(function (Throwable $exception): ErrorMessage {
                    if ($exception instanceof DomainException) {
                        return new FakeErrorMessage('some error message', 1657197780);
                    }

                    throw $exception;
                })
                ->mapper()
                ->map(ClassThatThrowsExceptionIfInvalidValue::class, 'bar');
        } catch (MappingError $exception) {
            self::assertMappingErrors(
                $exception,
                [
                    '*root*' => "[1657197780] some error message",
                ],
                assertErrorsBodiesAreRegistered: false,
            );
        }
    }

    public function test_userland_exception_filtered_is_caught_in_interface_inferring_and_added_to_mapping_errors(): void
    {
        try {
            $this->mapperBuilder()
                ->filterExceptions(function (Throwable $exception): ErrorMessage {
                    if ($exception instanceof DomainException) {
                        return new FakeErrorMessage($exception->getMessage(), $exception->getCode());
                    }

                    throw $exception;
                })
                ->infer(
                    DateTimeInterface::class,
                    fn () => throw new DomainException('some domain error message', 1653990051)
                )
                ->mapper()
                ->map(DateTimeInterface::class, []);
        } catch (MappingError $exception) {
            self::assertMappingErrors(
                $exception,
                [
                    '*root*' => "[1653990051] some domain error message",
                ],
                assertErrorsBodiesAreRegistered: false,
            );
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
