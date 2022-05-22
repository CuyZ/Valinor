<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\Tree\Message\FakeErrorMessage;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;
use CuyZ\Valinor\Tests\Integration\Mapping\Fixture\SimpleObject;

final class VisitorMappingTest extends IntegrationTest
{
    public function test_visitors_are_called_during_mapping(): void
    {
        $visits = [];
        $error = new FakeErrorMessage();

        try {
            (new MapperBuilder())
                ->visit(function (Node $node) use (&$visits): void {
                    if ($node->isRoot()) {
                        $visits[] = '#1';
                    }
                })
                ->visit(function (Node $node) use (&$visits): void {
                    if ($node->isRoot()) {
                        $visits[] = '#2';
                    }
                })
                ->visit(function () use ($error): void {
                    throw $error;
                })
                ->mapper()
                ->map(SimpleObject::class, ['value' => 'foo']);
        } catch (MappingError $exception) {
            self::assertSame('some error message', (string)$exception->node()->messages()[0]);
        }

        self::assertSame(['#1', '#2'], $visits);
    }
}
