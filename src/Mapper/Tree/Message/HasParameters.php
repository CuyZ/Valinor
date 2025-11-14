<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message;

/**
 * This interface can be implemented by a message to provide parameters that can
 * be used as placeholders in the message's body.
 *
 * ```
 * use CuyZ\Valinor\Mapper\Tree\Message\Message;
 * use CuyZ\Valinor\Mapper\Tree\Message\HasParameters;
 *
 * final class SomeMessage implements Message, HasParameters
 * {
 *     private string $someParameter;
 *
 *     public function __construct(string $someParameter)
 *     {
 *         $this->someParameter = $someParameter;
 *     }
 *
 *     public function body(): string
 *     {
 *         return 'Some message with {some_parameter}';
 *     }
 *
 *     public function parameters(): array
 *     {
 *         return [
 *             'some_parameter' => $this->someParameter,
 *         ];
 *     }
 * }
 * ```
 *
 * @api
 */
interface HasParameters extends Message
{
    /**
     * @pure
     * @return array<string, string>
     */
    public function parameters(): array;
}
