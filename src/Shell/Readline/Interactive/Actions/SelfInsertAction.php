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
 * Insert a character at the cursor position.
 */
class SelfInsertAction implements ActionInterface
{
    private string $char;

    public function __construct(string $char)
    {
        $this->char = $char;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Buffer $buffer, Terminal $terminal, Readline $readline): bool
    {
        $context = $buffer->getText();
        $buffer->autoDedentIfClosingBracket($this->char, $context);

        $buffer->insert($this->char);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'self-insert';
    }
}
