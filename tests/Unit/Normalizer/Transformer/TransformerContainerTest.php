<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Normalizer\Transformer;

use CuyZ\Valinor\Normalizer\Transformer\TransformerContainer;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeFunctionDefinitionRepository;
use PHPUnit\Framework\TestCase;

final class TransformerContainerTest extends TestCase
{
    public function test_container_checks_transformers_only_once(): void
    {
        $functionDefinitionRepository = new FakeFunctionDefinitionRepository();

        $container = new TransformerContainer($functionDefinitionRepository, [
            fn (string $value) => 'foo',
            fn (string $value) => 'bar',
        ]);

        $container->transformers();
        $container->transformers();

        self::assertSame(2, $functionDefinitionRepository->callCount);
    }
}
