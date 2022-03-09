<?php

declare(strict_types=1);

namespace Utils\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Exception\PoorDocumentationException;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class UseSetViewParametersInsteadMagicMethod extends AbstractRector
{
    /**
     * @throws PoorDocumentationException
     */
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
    }

    public function refactor(Node $node): ?Node
    {
        $properties = [];
        $parent = $node;
        do {
            $parent = $parent->getAttribute('parent');
        } while (! $parent instanceof Class_);

        foreach ($parent->stmts as $statement) {
            if ($statement instanceof Node\Stmt\Property) {
                $properties[] = $statement->props[0]->name->name;
            }
        }

        if (empty($node->var->var->name)) {
            return null;
        }
        if ($node->var->var->name !== 'this') {
            return null;
        }

        $key = '';
        if (! empty($node->var->name->value)) {
            $key = $node->var->name->value;
        }
        if (! empty($node->var->name->name)) {
            $key = $node->var->name->name;
        }
        if (! $key) {
            return null;
        }
        if (in_array($key, $properties, true)) {
            return null;
        }
        return new MethodCall(
            new Node\Expr\Variable('this'),
            new Node\Identifier('setViewParameter'),
            [
                new Node\Scalar\String_($key),
                $node->expr,
            ]
        );
    }
}
