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

use LaraGram\Probe\Shell\Readline\Interactive\Input\Buffer;
use LaraGram\Probe\Shell\Readline\Interactive\Readline;
use LaraGram\Probe\Shell\Readline\Interactive\Terminal;

/**
 * Move cursor to end of current line (Ctrl-E, End).
 */
class MoveToEndAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Buffer $buffer, Terminal $terminal, Readline $readline): bool
    {
        $buffer->moveCursorToEndOfCurrentLine();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'move-to-end';
    }
}
