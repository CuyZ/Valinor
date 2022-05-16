<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use CuyZ\Valinor\Type;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Definition;

/** @internal */
final class RecursiveSignatureTypeDefinitionWarmup
{
    private Type\Parser\TypeParser $parser;

    private Definition\Repository\ClassDefinitionRepository $classDefinitionRepository;

    public function __construct(Type\Parser\TypeParser $parser, Definition\Repository\ClassDefinitionRepository $classDefinitionRepository)
    {
        $this->parser = $parser;
        $this->classDefinitionRepository = $classDefinitionRepository;
    }

    /**
     * @param list<class-string> $signatures
     * @param list<class-string> $alreadyKnownSignatures
     * @return list<class-string>
     * @throws InvalidType In case one of the provided signatures contain invalid types.
     */
    public function warmupSignatures(array $signatures, array $alreadyKnownSignatures): array
    {
        $signaturesWarmedUp = $alreadyKnownSignatures;
        $typeParser = $this->parser;

        foreach ($signatures as $signature) {
            if (in_array($signature, $signaturesWarmedUp, true)) {
                continue;
            }

            $type = $typeParser->parse($signature);
            $signaturesWarmedUp[] = $signature;

            $signaturesWarmedUp = $this->warmupSignatureByType($type, $signaturesWarmedUp);
        }

        return $signaturesWarmedUp;
    }

    /**
     * @param list<class-string> $alreadyKnownSignatures
     * @return list<class-string>
     */
    private function extractSignaturesFromClassDefinition(Definition\ClassDefinition $classDefinition, array $alreadyKnownSignatures): array
    {
        $recursiveSignatures = [];
        foreach ($classDefinition->properties() as $property) {
            $propertyType = $property->type();
            if (!$propertyType instanceof ClassType) {
                continue;
            }

            $className = $propertyType->className();
            if (in_array($className, $alreadyKnownSignatures, true)) {
                continue;
            }

            $recursiveSignatures[] = $className;
        }

        return $recursiveSignatures;
    }

    /**
     * @param list<class-string> $alreadyKnownSignatures
     *
     * @return list<class-string>
     * @throws InvalidType In case one of the provided signatures contain invalid types.
     */
    private function warmupSignatureByType(Type\Type $type, array $alreadyKnownSignatures): array
    {
        if ($type instanceof ClassType) {
            $classDefinition = $this->classDefinitionRepository->for($type);
            $recursiveSignaturesToWarmup = $this->extractSignaturesFromClassDefinition($classDefinition, $alreadyKnownSignatures);

            return $this->warmupSignatures($recursiveSignaturesToWarmup, $alreadyKnownSignatures);
        }

        if ($type instanceof CompositeType) {
            $types = $type;
            unset($type);
            foreach ($types->traverse() as $type) {
                $alreadyKnownSignatures = $this->warmupSignatureByType($type, $alreadyKnownSignatures);
            }

            return $alreadyKnownSignatures;
        }

        return $alreadyKnownSignatures;
    }
}