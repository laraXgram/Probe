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
 * Submit the current line for evaluation.
 */
class SubmitLineAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Buffer $buffer, Terminal $terminal, Readline $readline): bool
    {
        $line = $buffer->getText();

        // Move from current input line to below any outer frame rows.
        $lineCount = \substr_count($line, "\n") + 1;
        $remainingInputLines = $lineCount - $buffer->getCurrentLineNumber();
        $outerRows = $readline->getInputFrameOuterRowCount();
        $escapeRows = $remainingInputLines + $outerRows;
        $terminal->write(\str_repeat("\n", $escapeRows));

        $readline->setLastSubmitEscapeRows($escapeRows);

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'submit-line';
    }
}
