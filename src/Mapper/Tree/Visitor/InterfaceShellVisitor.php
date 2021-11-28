<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Visitor;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Resolver\Exception\CannotResolveTypeFromInterface;
use CuyZ\Valinor\Type\Resolver\Exception\InvalidInterfaceResolverReturnType;
use CuyZ\Valinor\Type\Resolver\Exception\InvalidTypeResolvedForInterface;
use CuyZ\Valinor\Type\Resolver\Exception\ResolvedTypeForInterfaceIsNotAccepted;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use DateTimeImmutable;
use DateTimeInterface;

use function is_string;

final class InterfaceShellVisitor implements ShellVisitor
{
    /** @var array<class-string, callable(Shell): class-string> */
    private array $interfaceMapping;

    private TypeParser $typeParser;

    /**
     * @param array<class-string, callable(Shell): class-string> $interfaceMapping
     */
    public function __construct(array $interfaceMapping, TypeParser $typeParser)
    {
        $this->interfaceMapping = $interfaceMapping;
        $this->typeParser = $typeParser;

        $this->interfaceMapping[DateTimeInterface::class] ??= static fn () => DateTimeImmutable::class;
    }

    public function visit(Shell $shell): Shell
    {
        $type = $shell->type();

        if (! $type instanceof InterfaceType) {
            return $shell;
        }

        $interfaceName = $type->signature()->className();

        if (! isset($this->interfaceMapping[$interfaceName])) {
            throw new CannotResolveTypeFromInterface($interfaceName);
        }

        $signature = ($this->interfaceMapping[$interfaceName])($shell);

        // @phpstan-ignore-next-line
        if (! is_string($signature)) {
            throw new InvalidInterfaceResolverReturnType($interfaceName, $signature);
        }

        $classType = $this->typeParser->parse($signature);

        if (! $classType instanceof ClassType) {
            throw new InvalidTypeResolvedForInterface($interfaceName, $classType);
        }

        if (! $type->matches($classType)) {
            throw new ResolvedTypeForInterfaceIsNotAccepted($interfaceName, $classType);
        }

        return $shell->withType($classType);
    }
}
