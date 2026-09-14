<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\Completion\Source;

use LaraGram\Probe\Shell\Completion\AnalysisResult;
use LaraGram\Probe\Shell\Completion\CompletionKind;
use LaraGram\Probe\Shell\Readline\Interactive\Input\History;

/**
 * History-based completion source.
 *
 * Provides completion candidates from command history at the start of input.
 */
class HistorySource implements SourceInterface
{
    private History $history;

    public function __construct(History $history)
    {
        $this->history = $history;
    }

    /**
     * {@inheritdoc}
     */
    public function appliesToKind(int $kinds): bool
    {
        return ($kinds & CompletionKind::COMMAND) !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompletions(AnalysisResult $analysis): array
    {
        if ($analysis->prefix === '') {
            return [];
        }

        // Newest first, deduplicated
        $commands = $this->history->search('', false);

        return \array_values(\array_unique($commands));
    }
}
