<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\CodeCleaner;

use LaraGram\Probe\PhpParser\Node;
use LaraGram\Probe\PhpParser\Node\Expr\ArrayDimFetch;
use LaraGram\Probe\PhpParser\Node\Expr\Isset_;
use LaraGram\Probe\PhpParser\Node\Expr\NullsafePropertyFetch;
use LaraGram\Probe\PhpParser\Node\Expr\PropertyFetch;
use LaraGram\Probe\PhpParser\Node\Expr\Variable;
use LaraGram\Probe\Shell\Exception\FatalErrorException;

/**
 * Code cleaner pass to ensure we only allow variables, array fetch and property
 * fetch expressions in isset() calls.
 */
class IssetPass extends CodeCleanerPass
{
    const EXCEPTION_MSG = 'Cannot use isset() on the result of an expression (you can use "null !== expression" instead)';

    /**
     * @throws FatalErrorException
     *
     * @param Node $node
     *
     * @return int|Node|null Replacement node (or special return value)
     */
    public function enterNode(Node $node)
    {
        if (!$node instanceof Isset_) {
            return null;
        }

        foreach ($node->vars as $var) {
            if (!$var instanceof Variable && !$var instanceof ArrayDimFetch && !$var instanceof PropertyFetch && !$var instanceof NullsafePropertyFetch) {
                throw new FatalErrorException(self::EXCEPTION_MSG, 0, \E_ERROR, null, $node->getStartLine());
            }
        }

        return null;
    }
}
