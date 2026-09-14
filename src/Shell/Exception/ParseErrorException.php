<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\Exception;

/**
 * A "parse error" Exception for Psy.
 */
class ParseErrorException extends \LaraGram\Probe\PhpParser\Error implements Exception
{
    /**
     * Constructor!
     *
     * @param string    $message    (default: '')
     * @param array|int $attributes Attributes of node/token where error occurred
     *                              (or start line of error -- deprecated)
     */
    public function __construct(string $message = '', $attributes = [])
    {
        $message = \sprintf('PHP Parse error: %s', $message);

        if (!\is_array($attributes)) {
            $attributes = ['startLine' => $attributes];
        }

        parent::__construct($message, $attributes);
    }

    /**
     * Create a ParseErrorException from a PhpParser Error.
     *
     * @param \LaraGram\Probe\PhpParser\Error $e
     */
    public static function fromParseError(\LaraGram\Probe\PhpParser\Error $e): self
    {
        return new self($e->getRawMessage(), $e->getAttributes());
    }
}
