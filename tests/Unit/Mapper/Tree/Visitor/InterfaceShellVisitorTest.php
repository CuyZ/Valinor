<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Visitor;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Mapper\Tree\Visitor\InterfaceShellVisitor;
use CuyZ\Valinor\Tests\Fake\Type\Parser\FakeTypeParser;
use CuyZ\Valinor\Type\Resolver\Exception\CannotResolveTypeFromInterface;
use CuyZ\Valinor\Type\Resolver\Exception\InvalidInterfaceResolverReturnType;
use CuyZ\Valinor\Type\Resolver\Exception\InvalidTypeResolvedForInterface;
use CuyZ\Valinor\Type\Resolver\Exception\ResolvedTypeForInterfaceIsNotAccepted;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;
use Throwable;

final class InterfaceShellVisitorTest extends TestCase
{
    private FakeTypeParser $typeParser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeParser = new FakeTypeParser();
    }

    public function test_class_type_is_resolved(): void
    {
        $shell = Shell::root(new InterfaceType(DateTimeInterface::class), 'foo');

        $classType = new ClassType(DateTime::class);
        $this->typeParser->willReturn(DateTime::class, $classType);

        $visitor = $this->visitor([
            DateTimeInterface::class => static function (Shell $shellParam) use ($shell): string {
                self::assertSame($shellParam, $shell);

                return DateTime::class;
            },
        ]);

        self::assertSame($classType, $visitor->visit($shell)->type());
    }

    public function test_non_mapped_interface_throws_exception(): void
    {
        $this->expectException(CannotResolveTypeFromInterface::class);
        $this->expectExceptionCode(1618049116);
        $this->expectExceptionMessage('Impossible to resolve an implementation for the interface `Throwable`.');

        $shell = Shell::root(new InterfaceType(Throwable::class), 'foo');

        $this->visitor([])->visit($shell);
    }

    public function test_wrong_return_type_throws_exception(): void
    {
        $this->expectException(InvalidInterfaceResolverReturnType::class);
        $this->expectExceptionCode(1630091260);
        $this->expectExceptionMessage('Invalid type `int`; it must be the name of a class that implements `DateTimeInterface`.');

        $shell = Shell::root(new InterfaceType(DateTimeInterface::class), 'foo');

        // @phpstan-ignore-next-line
        $this->visitor([
            DateTimeInterface::class => static fn () => 1337,
        ])->visit($shell);
    }

    public function test_wrong_type_resolved_throws_exception(): void
    {
        $this->expectException(InvalidTypeResolvedForInterface::class);
        $this->expectExceptionCode(1618049224);
        $this->expectExceptionMessage('Invalid type `bool`; it must be the name of a class that implements `DateTimeInterface`.');

        $shell = Shell::root(new InterfaceType(DateTimeInterface::class), 'foo');

        // @phpstan-ignore-next-line
        $this->visitor([
            DateTimeInterface::class => static fn () => 'bool',
        ])->visit($shell);
    }

    public function test_implementation_not_accepted_by_interface_throws_exception(): void
    {
        $this->expectException(ResolvedTypeForInterfaceIsNotAccepted::class);
        $this->expectExceptionCode(1618049487);
        $this->expectExceptionMessage('The implementation `stdClass` is not accepted by the interface `DateTimeInterface`.');

        $shell = Shell::root(new InterfaceType(DateTimeInterface::class), 'foo');

        $this->typeParser->willReturn(stdClass::class, new ClassType(stdClass::class));

        $this->visitor([
            DateTimeInterface::class => static fn () => stdClass::class,
        ])->visit($shell);
    }

    /**
     * @param array<class-string, callable(Shell): class-string> $callbacks
     */
    private function visitor(array $callbacks): InterfaceShellVisitor
    {
        return new InterfaceShellVisitor($callbacks, $this->typeParser);
    }
}
