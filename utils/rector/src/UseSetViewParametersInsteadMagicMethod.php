<?php

namespace Utils\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class UseSetViewParametersInsteadMagicMethod extends AbstractRector
{

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use $this->setViewParameter("key","value") instead of $request->{"key"} = "value"',
            []
        );
    }

    public function getNodeTypes(): array
    {
        return [Node\Expr\Assign::class];
        //return [MethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (empty($node->var->var->name)){
            return null;
        }
        if ($node->var->var->name != 'this') {
            return null;
        }

        if (! empty($node->var->name->value)) {
            $key = $node->var->name->value;
        }
        if (! empty($node->var->name->name)) {
            return null;
            $key = $node->var->name->name;
        }
        if (! $key) {
            return null;
        }
        $newNode = new MethodCall(
            new Node\Expr\Variable('this'),
            new Node\Identifier('setViewParameter'),
            [
                new Node\Scalar\String_($key),
                $node->expr,
            ]
        );
        return $newNode;
    }


}