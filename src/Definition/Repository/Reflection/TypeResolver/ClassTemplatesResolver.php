<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\Parser\Exception\Template\DuplicatedTemplateName;
use CuyZ\Valinor\Type\Parser\Lexer\Annotations;
use CuyZ\Valinor\Utility\Reflection\Reflection;

use function array_key_exists;
use function array_keys;
use function current;
use function key;

/** @internal */
final class ClassTemplatesResolver
{
    /**
     * @param class-string $className
     * @return list<non-empty-string>
     */
    public function resolveTemplateNamesFrom(string $className): array
    {
        return array_keys($this->resolveTemplatesFrom($className));
    }

    /**
     * @param class-string $className
     * @return array<non-empty-string, non-empty-string|null>
     */
    public function resolveTemplatesFrom(string $className): array
    {
        $docBlock = Reflection::class($className)->getDocComment();

        if ($docBlock === false) {
            return [];
        }

        $templates = [];

        $annotations = (new Annotations($docBlock))->filteredByPriority(
            '@phpstan-template',
            '@psalm-template',
            '@template',
        );

        foreach ($annotations as $annotation) {
            $tokens = $annotation->filtered();

            $name = current($tokens);

            if (array_key_exists($name, $templates)) {
                throw new DuplicatedTemplateName($className, $name);
            }

            $of = next($tokens);

            if ($of !== 'of') {
                // The keyword `of` was not found, the following tokens are
                // considered as comments, and we ignore them.
                $templates[$name] = null;
            } else {
                // The keyword `of` was found, the following tokens represent
                // the template type.
                next($tokens);

                $key = key($tokens);

                $templates[$name] = $key ? $annotation->allAfter($key) : null;
            }
        }

        return $templates;
    }
}
