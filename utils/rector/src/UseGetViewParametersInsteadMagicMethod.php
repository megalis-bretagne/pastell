<?php

declare(strict_types=1);

namespace Utils\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Exception\PoorDocumentationException;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class UseGetViewParametersInsteadMagicMethod extends AbstractRector
{
    /**
     * @throws PoorDocumentationException
     */
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Use $this->getViewParameter("key") instead of $this->{"key"}',
            []
        );
    }

    public function getNodeTypes(): array
    {
        return [Node\Expr\PropertyFetch::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node->var->name !== 'this') {
            return null;
        }

        if (empty($node->name->value)) {
            return null;
        }

        $value = $node->name->value;

        return new MethodCall(
            new Node\Expr\Variable('this'),
            new Node\Identifier('getViewParameterOrObject'),
            [
                new Node\Scalar\String_($value),
            ]
        );
    }
}
