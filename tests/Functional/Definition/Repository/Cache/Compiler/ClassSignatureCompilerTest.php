<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassSignatureCompiler;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\TypeCompiler;
use CuyZ\Valinor\Type\Types\BooleanType;
use CuyZ\Valinor\Type\Types\FloatType;
use Error;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassSignatureCompilerTest extends TestCase
{
    private ClassSignatureCompiler $classSignatureCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->classSignatureCompiler = new ClassSignatureCompiler(new TypeCompiler());
    }

    /**
     * @dataProvider signature_is_compiled_correctly_data_provider
     */
    public function test_signature_is_compiled_correctly(ClassSignature $signature): void
    {
        $code = $this->classSignatureCompiler->compile($signature);

        try {
            $compiledSignature = eval("return $code;");
        } catch (Error $exception) {
            self::fail($exception->getMessage());
        }

        self::assertInstanceOf(ClassSignature::class, $compiledSignature);
        self::assertSame((string)$signature, (string)$compiledSignature);
        self::assertSame($signature->generics(), $compiledSignature->generics());
    }

    public function signature_is_compiled_correctly_data_provider(): iterable
    {
        yield [new ClassSignature(stdClass::class)];
        yield [new ClassSignature(stdClass::class, [
            'TemplateA' => BooleanType::get(),
            'TemplateB' => FloatType::get(),
        ])];
    }
}
