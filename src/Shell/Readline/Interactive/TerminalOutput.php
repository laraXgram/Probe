<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\Readline\Interactive;

use LaraGram\Console\Formatter\OutputFormatterInterface;
use LaraGram\Console\Output\StreamOutput;

/**
 * StreamOutput used exclusively for interactive terminal UI rendering.
 *
 * This writes to the same underlying stream as the shell output, but bypasses
 * ShellOutput-specific behaviors like visible-output tracking.
 */
class TerminalOutput extends StreamOutput
{
    private StreamOutput $source;

    public function __construct(StreamOutput $source)
    {
        $this->source = $source;

        parent::__construct(
            $source->getStream(),
            $source->getVerbosity(),
            $source->isDecorated(),
            $source->getFormatter()
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param string|iterable<string> $messages
     */
    public function write($messages, $newline = false, $type = 0): void
    {
        $this->syncState();

        parent::write($messages, $newline, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(): OutputFormatterInterface
    {
        $this->syncState();

        return parent::getFormatter();
    }

    /**
     * Keep formatter and display flags aligned with the shell output.
     */
    private function syncState(): void
    {
        parent::setFormatter($this->source->getFormatter());
        parent::setDecorated($this->source->isDecorated());
        parent::setVerbosity($this->source->getVerbosity());
    }
}
