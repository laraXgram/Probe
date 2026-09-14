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
use LaraGram\Probe\Shell\Completion\SymbolCatalog;

/**
 * Namespace completion source.
 *
 * Provides completions for namespaces extracted from declared classes.
 */
class NamespaceSource implements SourceInterface
{
    private SymbolCatalog $catalog;

    public function __construct(?SymbolCatalog $catalog = null)
    {
        $this->catalog = $catalog ?? new SymbolCatalog();
    }

    /**
     * {@inheritdoc}
     */
    public function appliesToKind(int $kinds): bool
    {
        return ($kinds & CompletionKind::NAMESPACE) !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompletions(AnalysisResult $analysis): array
    {
        $prefix = $analysis->prefix;

        $namespaces = $this->catalog->getNamespaces();

        // Filter by prefix (case-insensitive)
        $matches = [];
        $lowerPrefix = \strtolower($prefix);

        foreach ($namespaces as $namespace) {
            if ($lowerPrefix === '' || \strpos(\strtolower($namespace), $lowerPrefix) === 0) {
                $matches[] = $namespace;
            }
        }

        \sort($matches);

        return $matches;
    }
}
