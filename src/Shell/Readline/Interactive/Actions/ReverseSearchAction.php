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

use LaraGram\Probe\Shell\Readline\Interactive\HistorySearch;
use LaraGram\Probe\Shell\Readline\Interactive\Input\Buffer;
use LaraGram\Probe\Shell\Readline\Interactive\Readline;
use LaraGram\Probe\Shell\Readline\Interactive\Terminal;

/**
 * Action for interactive reverse history search (Ctrl-R).
 */
class ReverseSearchAction implements ActionInterface
{
    private HistorySearch $search;

    public function __construct(HistorySearch $search)
    {
        $this->search = $search;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Buffer $buffer, Terminal $terminal, Readline $readline): bool
    {
        if ($this->search->isActive()) {
            $this->search->findNext();
        } else {
            $readline->clearSuggestion();
            $this->search->saveBuffer($buffer);
            $this->search->enter($buffer->getCurrentLineText());
            $readline->pushMode($this->search);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'reverse-search-history';
    }
}
