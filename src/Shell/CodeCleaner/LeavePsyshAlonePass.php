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
use LaraGram\Probe\PhpParser\Node\Expr\Variable;
use LaraGram\Probe\Shell\Exception\RuntimeException;

/**
 * Validate that the user input does not reference the `$__psysh__` variable.
 */
class LeavePsyshAlonePass extends CodeCleanerPass
{
    /**
     * Validate that the user input does not reference the `$__psysh__` variable.
     *
     * @throws RuntimeException if the user is messing with $__psysh__
     *
     * @param Node $node
     *
     * @return int|Node|null Replacement node (or special return value)
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Variable && $node->name === '__psysh__') {
            throw new RuntimeException('Don\'t mess with $__psysh__; bad things will happen');
        }

        return null;
    }
}
