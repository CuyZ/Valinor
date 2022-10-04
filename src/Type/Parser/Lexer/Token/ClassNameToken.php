<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Constant\ClassConstantCaseNotFound;
use CuyZ\Valinor\Type\Parser\Exception\Constant\MissingClassConstantCase;
use CuyZ\Valinor\Type\Parser\Exception\Constant\MissingClassConstantColon;
use CuyZ\Valinor\Type\Parser\Exception\Constant\MissingSpecificClassConstantCase;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\Factory\ValueTypeFactory;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;
use ReflectionClassConstant;

use function array_map;
use function array_values;
use function count;
use function explode;

/** @internal */
final class ClassNameToken implements TraversingToken
{
    /** @var ReflectionClass<object> */
    private ReflectionClass $reflection;

    /**
     * @param class-string $className
     */
    public function __construct(string $className)
    {
        $this->reflection = Reflection::class($className);
    }

    public function traverse(TokenStream $stream): Type
    {
        $constant = $this->classConstant($stream);

        if ($constant) {
            return $constant;
        }

        return $this->reflection->isInterface() || $this->reflection->isAbstract()
            ? new InterfaceType($this->reflection->name)
            : new ClassType($this->reflection->name);
    }

    public function symbol(): string
    {
        return $this->reflection->name;
    }

    private function classConstant(TokenStream $stream): ?Type
    {
        if ($stream->done() || ! $stream->next() instanceof ColonToken) {
            return null;
        }

        $case = $stream->forward();
        $missingColon = true;

        if (! $stream->done()) {
            $case = $stream->forward();

            $missingColon = ! $case instanceof ColonToken;
        }

        if (! $missingColon) {
            if ($stream->done()) {
                throw new MissingClassConstantCase($this->reflection->name);
            }

            $case = $stream->forward();
        }

        $symbol = $case->symbol();

        if ($symbol === '*') {
            throw new MissingSpecificClassConstantCase($this->reflection->name);
        }

        if ($missingColon) {
            throw new MissingClassConstantColon($this->reflection->name, $symbol);
        }

        $cases = [];

        if (! preg_match('/\*\s*\*/', $symbol)) {
            $finder = new CaseFinder($this->cases());
            $cases = $finder->matching(explode('*', $symbol));
        }

        if (empty($cases)) {
            throw new ClassConstantCaseNotFound($this->reflection->name, $symbol);
        }

        $cases = array_map(static fn ($value) => ValueTypeFactory::from($value), $cases);

        if (count($cases) > 1) {
            // @PHP8.0 remove `array_values`
            // @infection-ignore-all
            return new UnionType(...array_values($cases));
        }

        return reset($cases);
    }

    /**
     * @return array<string, mixed>
     */
    private function cases(): array
    {
        // @PHP8.0 use `getConstants(ReflectionClassConstant::IS_PUBLIC)`
        $cases = [];

        foreach ($this->reflection->getReflectionConstants() as $constant) {
            if (! $constant->isPublic()) {
                continue;
            }

            $cases[$constant->name] = $constant->getValue();
        }

        return $cases;
    }
}
