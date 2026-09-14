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
 * Kill text from cursor to end of line (Ctrl-K).
 */
class KillLineAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Buffer $buffer, Terminal $terminal, Readline $readline): bool
    {
        $buffer->deleteToEnd();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'kill-line';
    }
}
