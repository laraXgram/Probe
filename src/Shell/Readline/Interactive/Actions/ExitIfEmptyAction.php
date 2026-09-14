<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\Readline\Interactive\Actions;

use LaraGram\Probe\Shell\Exception\BreakException;
use LaraGram\Probe\Shell\Readline\Interactive\Input\Buffer;
use LaraGram\Probe\Shell\Readline\Interactive\Readline;
use LaraGram\Probe\Shell\Readline\Interactive\Terminal;

/**
 * Exit the session when the current buffer is empty.
 */
class ExitIfEmptyAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Buffer $buffer, Terminal $terminal, Readline $readline): bool
    {
        if (!$buffer->isEmpty()) {
            return false;
        }

        $readline->escapeCurrentFrameForAbort($buffer);

        throw new BreakException('Ctrl+D');
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'exit-if-empty';
    }
}
