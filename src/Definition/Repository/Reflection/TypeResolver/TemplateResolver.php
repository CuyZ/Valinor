<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Annotations;
use ReflectionClass;
use ReflectionFunctionAbstract;

use function array_key_exists;
use function array_keys;
use function array_search;
use function array_values;
use function count;
use function str_ends_with;

/** @internal */
final class TemplateResolver
{
    /**
     * @param ReflectionClass<covariant object>|ReflectionFunctionAbstract $reflection
     * @return array<non-empty-string, GenericType>
     */
    public function templatesFromDocBlock(ReflectionClass|ReflectionFunctionAbstract $reflection, string $signature, TypeParser $typeParser): array
    {
        $annotations = Annotations::forTemplates($reflection);

        $templates = [];

        $previousDefaultedTemplate = null;

        foreach ($annotations as $annotation) {
            $tokens = $annotation->filtered();

            $keys = array_keys($tokens);
            $values = array_values($tokens);

            $name = $values[0];

            $covariant = str_ends_with($annotation->name(), '-covariant');

            if (array_key_exists($name, $templates)) {
                $templates[$name] = new GenericType($name, UnresolvableType::forDuplicatedTemplateName($signature, $name), $covariant);

                continue;
            }

            $bound = MixedType::get();
            $default = null;

            $defaultPosition = array_search('=', $values, true);

            if ($defaultPosition !== false) {
                if (isset($values[$defaultPosition + 1])) {
                    $default = $typeParser->parse($annotation->allAfter($keys[$defaultPosition + 1]));
                } else {
                    // A trailing `=` with no type after it is invalid.
                    $templates[$name] = new GenericType($name, UnresolvableType::forTemplateWithEmptyDefault($signature, $name), $covariant);

                    $previousDefaultedTemplate = $name;

                    continue;
                }
            }

            if (($values[1] ?? null) === 'of') {
                $boundEnd = $default ? $defaultPosition : count($values);

                if ($boundEnd > 2) {
                    $bound = $typeParser->parse(
                        $default
                            ? $annotation->allBetween($keys[2], $keys[$defaultPosition])
                            : $annotation->allAfter($keys[2])
                    );
                }
            }

            if ($default === null && $previousDefaultedTemplate !== null) {
                $templates[$name] = new GenericType($name, UnresolvableType::forTemplateDefaultNotTrailing($signature, $name, $previousDefaultedTemplate), $covariant);

                continue;
            }

            if ($default !== null) {
                $previousDefaultedTemplate = $name;
            }

            if ($bound instanceof UnresolvableType) {
                $bound = $bound->forInvalidTemplateType($signature, $name);
            }

            if ($default instanceof UnresolvableType) {
                $default = $default->forInvalidTemplateType($signature, $name);
            }

            $templates[$name] = new GenericType($name, $bound, $covariant, $default);
        }

        return $templates;
    }
}
