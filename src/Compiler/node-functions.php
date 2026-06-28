<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ArrayNode;
use CuyZ\Valinor\Compiler\Native\CastNode;
use CuyZ\Valinor\Compiler\Native\ClassNameNode;
use CuyZ\Valinor\Compiler\Native\CloneNode;
use CuyZ\Valinor\Compiler\Native\ClosureNode;
use CuyZ\Valinor\Compiler\Native\ForEachNode;
use CuyZ\Valinor\Compiler\Native\FunctionCallNode;
use CuyZ\Valinor\Compiler\Native\IfNode;
use CuyZ\Valinor\Compiler\Native\LogicalAndNode;
use CuyZ\Valinor\Compiler\Native\LogicalOrNode;
use CuyZ\Valinor\Compiler\Native\MatchNode;
use CuyZ\Valinor\Compiler\Native\NegateNode;
use CuyZ\Valinor\Compiler\Native\NewClassNode;
use CuyZ\Valinor\Compiler\Native\ParameterDeclarationNode;
use CuyZ\Valinor\Compiler\Native\PropertyDeclarationNode;
use CuyZ\Valinor\Compiler\Native\ReturnNode;
use CuyZ\Valinor\Compiler\Native\ShortClosureNode;
use CuyZ\Valinor\Compiler\Native\TernaryNode;
use CuyZ\Valinor\Compiler\Native\ThrowNode;
use CuyZ\Valinor\Compiler\Native\ValueNode;
use CuyZ\Valinor\Compiler\Native\VariableNode;
use CuyZ\Valinor\Compiler\Native\YieldNode;

/**
 * @param array<Node> $assignments
 */
function array_(array $assignments = []): ArrayNode
{
    return new ArrayNode($assignments);
}

function anonymousClass(): AnonymousClassNode
{
    return new AnonymousClassNode();
}

/**
 * @param non-empty-string $name
 * @param array<Node> $arguments
 */
function call(string $name, array $arguments = []): Node
{
    return new FunctionCallNode($name, $arguments);
}

function castToArray(Node $node): Node
{
    return CastNode::toArray($node);
}

/**
 * @param class-string $className
 */
function className(string $className): Node
{
    return new ClassNameNode($className);
}

function clone_(Node $node): Node
{
    return new CloneNode($node);
}

/**
 * @param array<Node> $body
 * @param list<non-empty-string> $uses
 */
function closure(array $body, array $uses = []): ClosureNode
{
    $node = new ClosureNode(...$body);

    if ($uses !== []) {
        $node = $node->uses(...$uses);
    }

    return $node;
}

/**
 * @param non-empty-string $key
 * @param non-empty-string $item
 */
function forEach_(Node $value, string $key, string $item, Node $body): ForEachNode
{
    return new ForEachNode($value, $key, $item, $body);
}

function if_(Node $condition, Node $body): IfNode
{
    return new IfNode($condition, $body);
}

/**
 * @no-named-arguments
 */
function logicalAnd(Node ...$nodes): Node
{
    return new LogicalAndNode(...$nodes);
}

/**
 * @no-named-arguments
 */
function logicalOr(Node ...$nodes): Node
{
    return new LogicalOrNode(...$nodes);
}

function match_(Node $value): MatchNode
{
    return new MatchNode($value);
}

function negate(Node $node): NegateNode
{
    return new NegateNode($node);
}

/**
 * @param class-string $className
 */
function newClass(string $className, Node ...$arguments): NewClassNode
{
    return new NewClassNode($className, ...$arguments);
}

/**
 * @param non-empty-string $name
 */
function param(string $name, string $type): ParameterDeclarationNode
{
    return new ParameterDeclarationNode($name, $type);
}

/**
 * @param non-empty-string $name
 */
function property(string $name, string $type): PropertyDeclarationNode
{
    return new PropertyDeclarationNode($name, $type);
}

function return_(?Node $node = null): ReturnNode
{
    return new ReturnNode($node);
}

/**
 * @param array<ParameterDeclarationNode> $parameters
 */
function shortClosure(
    Node $return,
    array $parameters = [],
): ShortClosureNode {
    $node = new ShortClosureNode($return);

    if ($parameters !== []) {
        $node = $node->witParameters(...$parameters);
    }

    return $node;
}

function ternary(Node $condition, Node $ifTrue, Node $ifFalse): TernaryNode
{
    return new TernaryNode($condition, $ifTrue, $ifFalse);
}

function this(): Node
{
    return variable('this');
}

function throw_(Node $node): ThrowNode
{
    return new ThrowNode($node);
}

/**
 * @param array<mixed>|bool|float|int|string|null $value
 */
function value(array|bool|float|int|string|null $value): Node
{
    return new ValueNode($value);
}

function variable(string $name): Node
{
    return new VariableNode($name);
}

function yield_(Node $key, Node $value): YieldNode
{
    return new YieldNode($key, $value);
}
