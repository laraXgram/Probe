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

use LaraGram\Probe\Shell\Command\Command;
use LaraGram\Probe\Shell\CommandArgumentCompletionAware;
use LaraGram\Probe\Shell\CommandAware;
use LaraGram\Probe\Shell\CommandMapTrait;
use LaraGram\Probe\Shell\Completion\AnalysisResult;
use LaraGram\Probe\Shell\Completion\CompletionKind;

/**
 * Command positional argument completion source.
 *
 * Delegates argument-tail completion to commands that explicitly opt in.
 */
class CommandArgumentSource implements CommandAware, SourceInterface
{
    use CommandMapTrait;

    /**
     * @param Command[] $commands Array of PsySH commands
     */
    public function __construct(array $commands)
    {
        $this->setCommands($commands);
    }

    /**
     * {@inheritdoc}
     */
    public function appliesToKind(int $kinds): bool
    {
        return ($kinds & CompletionKind::COMMAND_ARGUMENT) !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompletions(AnalysisResult $analysis): array
    {
        $commandName = \is_string($analysis->leftSide) ? $analysis->leftSide : null;
        $command = $commandName !== null ? ($this->commandMap[$commandName] ?? null) : null;

        if (!$command instanceof CommandArgumentCompletionAware) {
            return [];
        }

        // CommandContextRefiner already verified supportsArgumentCompletion()
        // before setting COMMAND_ARGUMENT kind.
        return $command->getArgumentCompletions($analysis);
    }
}
