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
use LaraGram\Probe\PhpParser\Node\Expr\FuncCall;
use LaraGram\Probe\PhpParser\Node\Name;
use LaraGram\Probe\PhpParser\Node\Scalar\MagicConst\Dir;
use LaraGram\Probe\PhpParser\Node\Scalar\MagicConst\File;
use LaraGram\Probe\PhpParser\Node\Scalar\String_;

/**
 * Swap out __DIR__ and __FILE__ magic constants with our best guess?
 */
class MagicConstantsPass extends CodeCleanerPass
{
    /**
     * Swap out __DIR__ and __FILE__ constants, because the default ones when
     * calling eval() don't make sense.
     *
     * @param Node $node
     *
     * @return FuncCall|String_|null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Dir) {
            return new FuncCall(new Name('getcwd'), [], $node->getAttributes());
        } elseif ($node instanceof File) {
            return new String_('', $node->getAttributes());
        }

        return null;
    }
}
