<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\GenericType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\Generic\AssignedGenericNotFound;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidAssignedGeneric;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidClassTemplate;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\AliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Utility\Reflection\DocParser;
use CuyZ\Valinor\Utility\Reflection\Reflection;

use function array_keys;

/** @internal */
final class GenericCheckerParser implements TypeParser
{
    public function __construct(
        private TypeParser $delegate,
        private TypeParserFactory $typeParserFactory,
    ) {}

    public function parse(string $raw): Type
    {
        $type = $this->delegate->parse($raw);

        $this->checkGenerics($type);

        return $type;
    }

    private function checkGenerics(Type $type): void
    {
        if ($type instanceof CompositeTraversableType) {
            foreach ($type->traverse() as $subType) {
                $this->checkGenerics($subType);
            }
        }

        if (! $type instanceof GenericType) {
            return;
        }

        $reflection = Reflection::class($type->className());
        $templates = DocParser::classTemplates($reflection);

        if ($templates === []) {
            return;
        }

        $generics = $type->generics();

        $parser = $this->typeParserFactory->get(
            new ClassContextSpecification($reflection->name),
            new AliasSpecification($reflection),
        );

        foreach ($templates as $templateName => $template) {
            if (! isset($generics[$templateName])) {
                throw new AssignedGenericNotFound($reflection->name, ...array_keys($templates));
            }

            array_shift($templates);

            if ($template === null) {
                // If no template is provided, it defaults to mixed type.
                continue;
            }

            $genericType = $generics[$templateName];

            try {
                $templateType = $parser->parse($template);
            } catch (InvalidType $invalidType) {
                throw new InvalidClassTemplate($reflection->name, $templateName, $invalidType);
            }

            if ($templateType instanceof ArrayKeyType && $genericType instanceof StringType) {
                $genericType = ArrayKeyType::string();
            }

            if ($templateType instanceof ArrayKeyType && $genericType instanceof IntegerType) {
                $genericType = ArrayKeyType::integer();
            }

            if (! $genericType->matches($templateType)) {
                throw new InvalidAssignedGeneric($genericType, $templateType, $templateName, $type->className());
            }
        }
    }
}
