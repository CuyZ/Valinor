<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Normalizer\Transformer\Compiler;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinition;
use CuyZ\Valinor\Tests\Fake\Normalizer\Transformer\Compiler\TypeFormatter\FakeTypeFormatter;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;

final class TransformerDefinitionTest extends UnitTestCase
{
    public function test_modifiers_return_clone_instances(): void
    {
        $definitionA = new TransformerDefinition(
            new FakeType(),
            [],
            new FakeTypeFormatter(),
        );

        $definitionB = $definitionA->withTransformerAttributes([]);
        $definitionC = $definitionA->markAsSure();

        self::assertNotSame($definitionA, $definitionB);
        self::assertNotSame($definitionA, $definitionC);
    }
}
