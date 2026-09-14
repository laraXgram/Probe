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
use LaraGram\Probe\Shell\CommandAware;
use LaraGram\Probe\Shell\CommandMapTrait;
use LaraGram\Probe\Shell\Completion\AnalysisResult;
use LaraGram\Probe\Shell\Completion\CompletionKind;
use LaraGram\Console\Input\StringInput;

/**
 * Command option/argument completion source.
 *
 * Provides completions for PsySH command options (e.g., --option, -o) and arguments.
 */
class CommandOptionSource implements CommandAware, SourceInterface
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
        return ($kinds & CompletionKind::COMMAND_OPTION) !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompletions(AnalysisResult $analysis): array
    {
        $commandName = $analysis->leftSide;
        if (!\is_string($commandName) || !isset($this->commandMap[$commandName])) {
            return [];
        }

        $command = $this->commandMap[$commandName];
        $input = $this->createInput($analysis);
        $matches = [];

        foreach ($command->getDefinition()->getOptions() as $option) {
            $longName = '--'.$option->getName();
            $shortName = $option->getShortcut() !== null ? '-'.$option->getShortcut() : null;

            if (!$option->isArray() && $this->isOptionUsed($input, $analysis, $longName, $shortName)) {
                continue;
            }

            $matches[] = $longName;

            if ($shortName !== null) {
                $matches[] = $shortName;
            }
        }

        \sort($matches);

        return $matches;
    }

    /**
     * Create a StringInput for token scanning, or null if input is empty/unparseable.
     */
    private function createInput(AnalysisResult $analysis): ?StringInput
    {
        if ($analysis->input === '') {
            return null;
        }

        try {
            return new StringInput($analysis->input);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check whether an option has already been used in the input.
     */
    private function isOptionUsed(?StringInput $input, AnalysisResult $analysis, string $longName, ?string $shortName): bool
    {
        if ($input === null) {
            return false;
        }

        $names = [$longName];
        if ($shortName !== null) {
            $names[] = $shortName;
        }

        if ($input->hasParameterOption($names, true)) {
            return true;
        }

        // hasParameterOption handles --long, --long=val, and -o as the
        // first short option in a group, but not combined short options
        // like -l in -al. Fall back to a simple string match for that.
        if ($shortName !== null && \preg_match('/(^|\s)-\w*'.\preg_quote($shortName[1], '/').'/', $analysis->input)) {
            return true;
        }

        return false;
    }
}
