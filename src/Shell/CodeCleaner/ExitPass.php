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
use LaraGram\Probe\PhpParser\Node\Arg;
use LaraGram\Probe\PhpParser\Node\Expr\Exit_;
use LaraGram\Probe\PhpParser\Node\Expr\StaticCall;
use LaraGram\Probe\PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
use LaraGram\Probe\Shell\Exception\BreakException;

class ExitPass extends CodeCleanerPass
{
    /**
     * Converts exit calls to BreakExceptions.
     *
     * @param \LaraGram\Probe\PhpParser\Node $node
     *
     * @return int|Node|Node[]|null Replacement node (or special return value)
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Exit_) {
            $args = $node->expr ? [new Arg($node->expr)] : [];

            return new StaticCall(new FullyQualifiedName(BreakException::class), 'exitShell', $args);
        }

        return null;
    }
}
