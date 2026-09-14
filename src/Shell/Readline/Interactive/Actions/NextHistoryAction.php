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
use LaraGram\Probe\Shell\Readline\Interactive\Input\History;
use LaraGram\Probe\Shell\Readline\Interactive\Readline;
use LaraGram\Probe\Shell\Readline\Interactive\Terminal;

/**
 * Navigate to next history entry (Down arrow).
 *
 * Fish/ZSH-style behavior: first moves within soft-wrapped visual rows, then
 * in multi-line mode moves to the next logical line. Only navigates to next
 * history entry when already at the bottom of the buffer.
 */
class NextHistoryAction implements ActionInterface
{
    private History $history;

    public function __construct(History $history)
    {
        $this->history = $history;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Buffer $buffer, Terminal $terminal, Readline $readline): bool
    {
        if ($buffer->moveToNextVisualRow(
            $terminal->getWidth(),
            $readline->getPromptWidthForCurrentLine($buffer)
        )) {
            return true;
        }

        if ($readline->isMultilineMode() && !$buffer->isOnLastLine()) {
            $buffer->moveToNextLine();

            return true;
        }

        if (!$this->history->isInHistory()) {
            $terminal->bell();

            return true;
        }

        $entry = $this->history->getNext();

        if ($entry !== null) {
            $buffer->setText($entry);
        } else {
            $terminal->bell();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'next-history';
    }
}
