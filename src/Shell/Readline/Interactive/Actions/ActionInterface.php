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
 * Action interface.
 *
 * Represents an action that can be executed in response to user input.
 */
interface ActionInterface
{
    /**
     * Execute the action.
     *
     * @return bool True if the readline loop should continue, false to break
     */
    public function execute(Buffer $buffer, Terminal $terminal, Readline $readline): bool;

    /**
     * Get the action name.
     *
     * @return string
     */
    public function getName(): string;
}
