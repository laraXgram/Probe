<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\Logger;

/**
 * Callback logger.
 *
 * A simple logger that calls a callback with the log kind and data.
 */
class CallbackLogger
{
    private \Closure $callback;

    /**
     * @param callable $callback Callback to invoke with (string $kind, string $data)
     */
    public function __construct(callable $callback)
    {
        $this->callback = \Closure::fromCallable($callback);
    }

    /**
     * Log a message.
     *
     * @param string $level   Log level
     * @param string $message Log message
     * @param array  $context Context data
     */
    public function log(string $level, string $message, array $context = []): void
    {
        // Determine the kind from the message
        switch ($message) {
            case 'Probe input':
                $kind = 'input';
                break;
            case 'Probe command':
                $kind = 'command';
                break;
            case 'Probe execute':
                $kind = 'execute';
                break;
            default:
                $kind = 'unknown';
                break;
        }

        // Extract the data from context
        $data = $context[$kind] ?? $context['code'] ?? '';

        \call_user_func($this->callback, $kind, $data);
    }
}
