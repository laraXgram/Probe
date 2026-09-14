<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\Readline\Interactive\Input;

use LaraGram\Probe\Shell\Readline\Interactive\Actions\AcceptSuggestionAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\AcceptSuggestionWordAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\ActionInterface;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\ClearBufferAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\ClearScreenAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\DedentLeadingIndentationAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\DeleteBackwardCharAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\DeleteBracketPairAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\DeleteForwardAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\ExitIfEmptyAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\FallbackAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\InsertCloseBracketAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\InsertLineBreakAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\InsertLineBreakOnIncompleteStatementAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\InsertLineBreakOnUnclosedBracketsAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\InsertOpenBracketAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\InsertQuoteAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\KillLineAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\KillWholeLineAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\KillWordAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\MoveLeftAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\MoveRightAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\MoveToEndAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\MoveToStartAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\MoveWordLeftAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\MoveWordRightAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\NextHistoryAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\PreviousHistoryAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\RejectSyntaxErrorAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\ReverseSearchAction;
use LaraGram\Probe\Shell\Readline\Interactive\Actions\SubmitLineAction;
use LaraGram\Probe\Shell\Readline\Interactive\HistorySearch;

/**
 * Keybindings registry, maps terminal key sequences to actions.
 */
class KeyBindings
{
    /** @var ActionInterface[] Keyed by key pattern */
    private array $bindings = [];

    /**
     * Bind a key to one or more actions.
     *
     * Multiple actions are executed as a fallback chain.
     *
     * @param string $keyPattern Key pattern (e.g., 'char:a', 'control:a', 'escape:[A')
     */
    public function bind(string $keyPattern, ActionInterface $action, ActionInterface ...$fallbackActions): void
    {
        $this->bindings[$keyPattern] = empty($fallbackActions) ? $action : new FallbackAction([$action, ...$fallbackActions]);
    }

    /**
     * Get the action bound to a key.
     */
    public function get(Key $key): ?ActionInterface
    {
        return $this->bindings[(string) $key] ?? null;
    }

    /**
     * Get all bindings.
     *
     * @return ActionInterface[] Keyed by key pattern
     */
    public function getAll(): array
    {
        return $this->bindings;
    }

    /**
     * Create default Emacs-style keybindings.
     *
     * @param bool $smartBrackets Enable smart bracket pairing
     */
    public static function createDefault(History $history, HistorySearch $search, bool $smartBrackets = false): self
    {
        $bindings = new self();

        // Enter/Return
        $acceptLine = new FallbackAction([
            new RejectSyntaxErrorAction(),
            new InsertLineBreakOnUnclosedBracketsAction($smartBrackets),
            new InsertLineBreakOnIncompleteStatementAction(),
            new SubmitLineAction(),
        ], false);
        $bindings->bind('char:'."\n", $acceptLine);
        $bindings->bind('char:'."\r", $acceptLine);
        // Shift+Enter variants (CSI-u / modifyOtherKeys)
        $bindings->bind('escape:[13;2u', new InsertLineBreakAction());
        $bindings->bind('escape:[13;2~', new InsertLineBreakAction());
        $bindings->bind('escape:[27;2;13~', new InsertLineBreakAction());
        // Shift+Enter remapped by some terminal setups (Esc+Enter)
        $bindings->bind('escape:'."\r", new InsertLineBreakAction());
        $bindings->bind('escape:'."\n", new InsertLineBreakAction());

        // Backspace
        $backspace = $smartBrackets
            ? new FallbackAction([new DedentLeadingIndentationAction(), new DeleteBracketPairAction(), new DeleteBackwardCharAction()])
            : new FallbackAction([new DedentLeadingIndentationAction(), new DeleteBackwardCharAction()]);
        $bindings->bind('control:h', $backspace);
        $bindings->bind('control:?', $backspace);

        // Delete
        $bindings->bind('escape:[3~', new DeleteForwardAction());

        // Arrow keys
        $bindings->bind('escape:[D', new MoveLeftAction());
        $bindings->bind('escape:[C', new AcceptSuggestionAction(), new MoveRightAction());
        $bindings->bind('escape:[A', new PreviousHistoryAction($history));
        $bindings->bind('escape:[B', new NextHistoryAction($history));

        // Emacs cursor movement
        $bindings->bind('control:b', new MoveLeftAction());
        $bindings->bind('control:f', new AcceptSuggestionAction(), new MoveRightAction());
        $bindings->bind('control:a', new MoveToStartAction());
        $bindings->bind('control:e', new MoveToEndAction());

        // Word movement - Alt+Arrow
        $bindings->bind('escape:[1;3D', new MoveWordLeftAction());
        $bindings->bind('escape:[1;3C', new AcceptSuggestionWordAction(), new MoveWordRightAction());

        // Word movement - Ctrl+Arrow
        $bindings->bind('escape:[1;5D', new MoveWordLeftAction());
        $bindings->bind('escape:[1;5C', new MoveWordRightAction());

        // Word movement - Emacs style
        $bindings->bind('escape:b', new MoveWordLeftAction());
        $bindings->bind('escape:f', new AcceptSuggestionWordAction(), new MoveWordRightAction());

        // Home/End keys
        $bindings->bind('escape:[H', new MoveToStartAction());
        $bindings->bind('escape:[F', new MoveToEndAction());
        $bindings->bind('escape:[1~', new MoveToStartAction());
        $bindings->bind('escape:[4~', new MoveToEndAction());

        // Kill operations
        $bindings->bind('control:k', new KillLineAction());
        $bindings->bind('control:u', new KillWholeLineAction());
        $bindings->bind('control:w', new KillWordAction());

        // Buffer control
        $bindings->bind('control:c', new ClearBufferAction());
        $bindings->bind('control:d', new ExitIfEmptyAction(), new DeleteForwardAction());

        // History search
        $bindings->bind('control:r', new ReverseSearchAction($search));

        // Clear screen
        $bindings->bind('control:l', new ClearScreenAction());

        // Smart bracket pairing
        if ($smartBrackets) {
            // Opening brackets
            $bindings->bind('char:(', new InsertOpenBracketAction('('));
            $bindings->bind('char:[', new InsertOpenBracketAction('['));
            $bindings->bind('char:{', new InsertOpenBracketAction('{'));

            // Closing brackets
            $bindings->bind('char:)', new InsertCloseBracketAction(')'));
            $bindings->bind('char:]', new InsertCloseBracketAction(']'));
            $bindings->bind('char:}', new InsertCloseBracketAction('}'));

            // Quotes
            $bindings->bind('char:"', new InsertQuoteAction('"'));
            $bindings->bind("char:'", new InsertQuoteAction("'"));
        }

        return $bindings;
    }
}
