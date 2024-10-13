<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Compiler;

use CuyZ\Valinor\Compiler\Native\AggregateNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\AnonymousFunctionNode;
use CuyZ\Valinor\Compiler\Native\ArrayNode;
use CuyZ\Valinor\Compiler\Native\ExpressionNode;
use CuyZ\Valinor\Compiler\Native\ForEachNode;
use CuyZ\Valinor\Compiler\Native\FunctionCallNode;
use CuyZ\Valinor\Compiler\Native\IfNode;
use CuyZ\Valinor\Compiler\Native\MatchNode;
use CuyZ\Valinor\Compiler\Native\MethodNode;
use CuyZ\Valinor\Compiler\Native\NegateNode;
use CuyZ\Valinor\Compiler\Native\NewClassNode;
use CuyZ\Valinor\Compiler\Native\ParameterDeclarationNode;
use CuyZ\Valinor\Compiler\Native\PropertyDeclarationNode;
use CuyZ\Valinor\Compiler\Native\PropertyNode;
use CuyZ\Valinor\Compiler\Native\ReturnNode;
use CuyZ\Valinor\Compiler\Native\ShortClosureNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Native\ValueNode;
use CuyZ\Valinor\Compiler\Native\VariableNode;
use CuyZ\Valinor\Compiler\Native\WrapNode;
use CuyZ\Valinor\Compiler\Native\YieldNode;

/** @internal */
abstract class Node
{
    abstract public function compile(Compiler $compiler): Compiler;

    // @todo rename to Statement?
    public function asExpression(): ExpressionNode
    {
        return new ExpressionNode($this);
    }

    public function wrap(): CompliantNode
    {
        return new CompliantNode(new WrapNode($this));
    }

    public static function aggregate(Node ...$nodes): AggregateNode
    {
        return new AggregateNode(...$nodes);
    }

    public static function anonymousFunction(): AnonymousFunctionNode
    {
        return new AnonymousFunctionNode();
    }

    /**
     * @param array<Node> $assignments
     */
    public static function array(array $assignments = []): ArrayNode
    {
        return new ArrayNode($assignments);
    }

    public static function anonymousClass(): AnonymousClassNode
    {
        return new AnonymousClassNode();
    }

    public static function forEach(Node $value, string $item, Node $body, ?string $key = null): ForEachNode
    {
        return new ForEachNode($value, $item, $body, $key);
    }

    /**
     * @param array<Node> $arguments
     */
    public static function functionCall(string $name, array $arguments = []): FunctionCallNode
    {
        return new FunctionCallNode($name, $arguments);
    }

    public static function if(Node $condition, Node $body): IfNode
    {
        return new IfNode($condition, $body);
    }

    public static function match(Node $value): MatchNode
    {
        return new MatchNode($value);
    }

    /**
     * @param non-empty-string $name
     */
    public static function method(string $name): MethodNode
    {
        return new MethodNode($name);
    }

    public static function negate(Node $node): NegateNode
    {
        return new NegateNode($node);
    }

    /**
     * @param class-string $className
     */
    public static function newClass(string $className, Node ...$arguments): NewClassNode
    {
        return new NewClassNode($className, ...$arguments);
    }

    public static function parameterDeclaration(string $name, string $type): ParameterDeclarationNode
    {
        return new ParameterDeclarationNode($name, $type);
    }

    public static function property(string $name): CompliantNode
    {
        return new CompliantNode(new PropertyNode($name));
    }

    public static function propertyDeclaration(string $name, string $type): PropertyDeclarationNode
    {
        return new PropertyDeclarationNode($name, $type);
    }

    public static function return(Node $node): ReturnNode
    {
        return new ReturnNode($node);
    }

    public static function shortClosure(Node $return): ShortClosureNode
    {
        return new ShortClosureNode($return);
    }

    public static function this(): CompliantNode
    {
        return self::variable('this');
    }

    public static function value(bool|float|int|string|null $value): ValueNode
    {
        return new ValueNode($value);
    }

    public static function variable(string $name): CompliantNode
    {
        return new CompliantNode(new VariableNode($name));
    }

    public static function yield(Node $value, ?Node $key = null): YieldNode
    {
        return new YieldNode($value, $key);
    }
}
