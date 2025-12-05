<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use Attribute;

/**
 * This attribute allows a static method inside a class to be marked as a
 * constructor, that can be used by the mapper to instantiate the object. The
 * method must be public, static and return an instance of the class it is part
 * of.
 *
 * This attribute is a convenient replacement to the usage of the constructor
 * registration method:
 * {@see \CuyZ\Valinor\MapperBuilder::registerConstructor()}
 *
 * ```
 * final readonly class Email
 * {
 *     // When another constructor is registered for the class, the native
 *     // constructor is disabled. To enable it again, it is mandatory to
 *     // explicitly register it again.
 *     #[\CuyZ\Valinor\Mapper\Object\Constructor]
 *     public function __construct(public string $value) {}
 *
 *     #[\CuyZ\Valinor\Mapper\Object\Constructor]
 *     public static function createFrom(string $user, string $domainName): self
 *     {
 *         return new self($user . '@' . $domainName);
 *     }
 * }
 *
 * (new \CuyZ\Valinor\MapperBuilder())
 *     ->mapper()
 *     ->map(Email::class, [
 *         'userName' => 'john.doe',
 *         'domainName' => 'example.com',
 *     ]); // john.doe@example.com
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Constructor {}
