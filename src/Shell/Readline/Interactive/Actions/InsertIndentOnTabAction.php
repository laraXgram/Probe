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
 * Insert indentation when Tab is pressed in leading multiline whitespace.
 */
class InsertIndentOnTabAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Buffer $buffer, Terminal $terminal, Readline $readline): bool
    {
        if (!$readline->isMultilineMode()) {
            return false;
        }

        $line = $buffer->getCurrentLineText();
        $cursorInLine = $buffer->getCursorPositionInLine();
        $beforeCursor = \substr($line, 0, $cursorInLine);

        if (\trim($beforeCursor) !== '') {
            return false;
        }

        $spaces = $buffer->spacesToNextTabStop($cursorInLine);
        $buffer->insert(\str_repeat(' ', $spaces));

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'insert-indent-on-tab';
    }
}
