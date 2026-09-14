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
use LaraGram\Probe\Shell\Readline\Interactive\Input\WordNavigationPolicy;
use LaraGram\Probe\Shell\Readline\Interactive\Readline;
use LaraGram\Probe\Shell\Readline\Interactive\Terminal;

/**
 * Accept one word from the current suggestion.
 */
class AcceptSuggestionWordAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(Buffer $buffer, Terminal $terminal, Readline $readline): bool
    {
        $suggestion = $readline->getCurrentSuggestion();
        if ($suggestion === null) {
            return false;
        }

        // Word-accept currently supports append-only suggestions.
        if (!$suggestion->isAppendOnly($buffer->getCursor())) {
            return false;
        }

        $text = $suggestion->getAcceptText();
        $wordEnd = (new WordNavigationPolicy())->findNextWord($text, 0);
        $firstWord = \mb_substr($text, 0, $wordEnd);
        if ($firstWord === '') {
            return false;
        }

        $buffer->insert($firstWord);
        $readline->clearSuggestion();

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'accept-suggestion-word';
    }
}
