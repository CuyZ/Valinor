<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Type\Parser\Template;

use CuyZ\Valinor\Type\Parser\Template\TemplateParser;
use CuyZ\Valinor\Type\Parser\TypeParser;

final class FakeTemplateParser implements TemplateParser
{
    public function templates(string $source, TypeParser $typeParser): array
    {
        return [];
    }
}
