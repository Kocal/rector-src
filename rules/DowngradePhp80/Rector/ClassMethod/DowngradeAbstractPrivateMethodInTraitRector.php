<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector\DowngradeAbstractPrivateMethodInTraitRectorTest
 */
final class DowngradeAbstractPrivateMethodInTraitRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove "abstract" from private methods in traits and adds an empty function body',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
trait SomeTrait
{
    abstract private function someAbstractPrivateFunction();
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
trait SomeTrait
{
    private function someAbstractPrivateFunction() {}
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $this->visibilityManipulator->removeAbstract($node);
        // Add empty array for stmts to generate empty function body
        $node->stmts = [];

        return $node;
    }

    private function shouldSkip(ClassMethod $node): bool
    {
        if (! $node->isAbstract()) {
            return true;
        }

        if (! $node->isPrivate()) {
            return true;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Trait_) {
            return true;
        }

        return false;
    }
}
