<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\Completion;

use LaraGram\Probe\PhpParser\Node;
use LaraGram\Probe\PhpParser\Node\Stmt\Expression;
use LaraGram\Probe\PhpParser\NodeTraverser;
use LaraGram\Probe\PhpParser\NodeVisitorAbstract;

/**
 * Visitor to find the top-level expression node in the statement.
 *
 * We want the outermost expression (PropertyFetch, MethodCall, New_, etc.)
 * not their child nodes. For a statement like `$baz->format;`, we want the
 * PropertyFetch node, not the Variable node inside it.
 */
class DeepestNodeVisitor extends NodeVisitorAbstract
{
    private ?Node $targetNode = null;

    public function enterNode(Node $node)
    {
        // If this is an Expression statement, grab its expression
        if ($node instanceof Expression) {
            $this->targetNode = $node->expr;

            // Don't traverse into the expression - we have what we need
            // @phan-suppress-next-line PhanDeprecatedClassConstant - keep compat with php-parser 4.x baseline
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        return null;
    }

    public function getDeepestNode(): ?Node
    {
        return $this->targetNode;
    }
}
