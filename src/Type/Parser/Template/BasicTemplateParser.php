<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Template;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Exception\Template\DuplicatedTemplateName;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidTemplateType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Types\MixedType;

use function preg_match_all;
use function trim;

/** @internal */
final class BasicTemplateParser implements TemplateParser
{
    public function templates(string $source, TypeParser $typeParser): array
    {
        $templates = [];

        preg_match_all("/@(phpstan-|psalm-)?template\s+(\w+)(\s+of\s+([\w\s?|&<>'\",-:\\\\\[\]{}]+))?/", $source, $raw);

        /** @var string[] $list */
        $list = $raw[2];

        if (empty($list)) {
            return [];
        }

        foreach ($list as $key => $name) {
            if (isset($templates[$name])) {
                throw new DuplicatedTemplateName($name);
            }

            $boundTypeSymbol = trim($raw[4][$key]);

            if (empty($boundTypeSymbol)) {
                $templates[$name] = MixedType::get();
                continue;
            }

            try {
                $templates[$name] = $typeParser->parse($boundTypeSymbol);
            } catch (InvalidType $invalidType) {
                throw new InvalidTemplateType($boundTypeSymbol, $name, $invalidType);
            }
        }

        return $templates;
    }
}
