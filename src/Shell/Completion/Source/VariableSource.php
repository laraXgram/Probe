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
use LaraGram\Probe\Shell\Context;

/**
 * Variable completion source.
 *
 * Provides completions for variables in the current scope.
 */
class VariableSource implements SourceInterface
{
    private Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function appliesToKind(int $kinds): bool
    {
        return ($kinds & CompletionKind::VARIABLE) !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompletions(AnalysisResult $analysis): array
    {
        // Always return all variables (typically small set, fuzzy matching will handle filtering)
        $variables = \array_keys($this->context->getAll());
        \sort($variables);

        return $variables;
    }
}
