<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Annotations;
use ReflectionClass;
use ReflectionFunctionAbstract;

use function array_key_exists;
use function current;
use function key;
use function next;

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

        foreach ($annotations as $annotation) {
            $tokens = $annotation->filtered();

            $name = current($tokens);

            if (array_key_exists($name, $templates)) {
                $templateType = UnresolvableType::forDuplicatedTemplateName($signature, $name);
            } else {
                $of = next($tokens);

                if ($of !== 'of') {
                    // The keyword `of` was not found, the following tokens are
                    // considered as comments, and we ignore them.
                    $templateType = MixedType::get();
                } else {
                    // The keyword `of` was found, the following tokens represent
                    // the template type.
                    next($tokens);

                    /** @var array<int, non-empty-string> $tokens */
                    $key = key($tokens);

                    if ($key === null) {
                        $templateType = MixedType::get();
                    } else {
                        $templateType = $typeParser->parse($annotation->allAfter($key));

                        if ($templateType instanceof UnresolvableType) {
                            $templateType = $templateType->forInvalidTemplateType($signature, $name);
                        }
                    }
                }
            }

            $templates[$name] = new GenericType($name, $templateType);
        }

        return $templates;
    }
}
