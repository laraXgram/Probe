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
use LaraGram\Probe\PhpParser\Node\Expr\MethodCall;
use LaraGram\Probe\PhpParser\Node\Expr\StaticCall;
use LaraGram\Probe\PhpParser\Node\VariadicPlaceholder;
use LaraGram\Probe\Shell\Exception\FatalErrorException;

/**
 * Validate that the user did not use the call-time pass-by-reference that causes a fatal error.
 *
 * As of PHP 5.4.0, call-time pass-by-reference was removed, so using it will raise a fatal error.
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 */
class CallTimePassByReferencePass extends CodeCleanerPass
{
    const EXCEPTION_MESSAGE = 'Call-time pass-by-reference has been removed';

    /**
     * Validate of use call-time pass-by-reference.
     *
     * @throws FatalErrorException if the user used call-time pass-by-reference
     *
     * @param Node $node
     *
     * @return int|Node|null Replacement node (or special return value)
     */
    public function enterNode(Node $node)
    {
        if (!$node instanceof FuncCall && !$node instanceof MethodCall && !$node instanceof StaticCall) {
            return null;
        }

        foreach ($node->args as $arg) {
            if ($arg instanceof VariadicPlaceholder) {
                continue;
            }

            if ($arg->byRef) {
                throw new FatalErrorException(self::EXCEPTION_MESSAGE, 0, \E_ERROR, null, $node->getStartLine());
            }
        }

        return null;
    }
}
