<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type\Parser\Factory;

use CuyZ\Valinor\Tests\Fake\Type\Parser\FakeTypeParser;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeParserSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\TypeParser;

final class FakeTypeParserFactory implements TypeParserFactory
{
    public function get(TypeParserSpecification ...$specifications): TypeParser
    {
        return new FakeTypeParser();
    }
}
