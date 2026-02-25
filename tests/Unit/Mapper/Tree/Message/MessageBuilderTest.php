<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Message;

use CuyZ\Valinor\Mapper\Tree\Message\HasCode;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use Exception;

final class MessageBuilderTest extends UnitTestCase
{
    public function test_body_can_be_retrieved(): void
    {
        $message = MessageBuilder::new('some message body');
        $messageError = MessageBuilder::newError('some error message body');

        self::assertSame('some message body', $message->body());
        self::assertSame('some message body', $message->build()->body());

        self::assertSame('some error message body', $messageError->body());
        self::assertSame('some error message body', $messageError->build()->body());

        $message = $message->withBody('some new message body');
        $messageError = $messageError->withBody('some new error message body');

        self::assertSame('some new message body', $message->body());
        self::assertSame('some new message body', $message->build()->body());

        self::assertSame('some new error message body', $messageError->body());
        self::assertSame('some new error message body', $messageError->build()->body());
    }

    public function test_code_can_be_retrieved(): void
    {
        $message = MessageBuilder::new('some message body');
        $message = $message->withCode('some_code');

        self::assertSame('some_code', $message->code());
        self::assertSame('some_code', $message->build()->code());
    }

    public function test_parameters_can_be_retrieved(): void
    {
        $message = MessageBuilder::new('some message body');
        $message = $message
            ->withParameter('parameter_a', 'valueA')
            ->withParameter('parameter_b', 'valueB');

        self::assertSame(['parameter_a' => 'valueA', 'parameter_b' => 'valueB'], $message->parameters());
        self::assertSame(['parameter_a' => 'valueA', 'parameter_b' => 'valueB'], $message->build()->parameters());
    }

    public function test_modifiers_return_clone_instances(): void
    {
        $messageA = MessageBuilder::new('some message body');
        $messageB = $messageA->withBody('some new message body');
        $messageC = $messageB->withCode('some_code');
        $messageD = $messageC->withParameter('parameter_a', 'valueA');

        self::assertNotSame($messageA, $messageB);
        self::assertNotSame($messageB, $messageC);
        self::assertNotSame($messageC, $messageD);
    }

    public function test_from_throwable_build_error_message(): void
    {
        $exception = new Exception('some error message', 1664450422);

        $message = MessageBuilder::from($exception);

        self::assertSame('some error message', $message->body());
        self::assertInstanceOf(HasCode::class, $message);
        self::assertSame('1664450422', $message->code());
    }

    public function test_from_throwable_build_error_message_without_code(): void
    {
        $exception = new Exception('some error message');

        $message = MessageBuilder::from($exception);

        self::assertInstanceOf(HasCode::class, $message);
        self::assertSame('unknown', $message->code());
    }

    public function test_from_error_message_returns_same_instance(): void
    {
        $error = new FakeErrorMessage();
        $message = MessageBuilder::from($error);

        self::assertSame($error, $message);
    }
}
