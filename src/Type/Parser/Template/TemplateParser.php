<?php

namespace CuyZ\Valinor\Type\Parser\Template;

use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidTemplate;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;

/** @internal */
interface TemplateParser
{
    /**
     * @return array<string, Type>
     *
     * @throws InvalidTemplate
     */
    public function templates(string $source, TypeParser $typeParser): array;
}
