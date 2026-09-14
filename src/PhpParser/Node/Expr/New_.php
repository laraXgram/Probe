<?php declare(strict_types=1);

namespace LaraGram\Probe\PhpParser\Node\Expr;

use LaraGram\Probe\PhpParser\Node;
use LaraGram\Probe\PhpParser\Node\Arg;
use LaraGram\Probe\PhpParser\Node\ArgPlaceholder;
use LaraGram\Probe\PhpParser\Node\Expr;
use LaraGram\Probe\PhpParser\Node\VariadicPlaceholder;

class New_ extends CallLike {
    /** @var Node\Name|Expr|Node\Stmt\Class_ Class name */
    public Node $class;
    /** @var array<Arg|VariadicPlaceholder|ArgPlaceholder> Arguments */
    public array $args;

    /**
     * Constructs a function call node.
     *
     * @param Node\Name|Expr|Node\Stmt\Class_ $class Class name (or class node for anonymous classes)
     * @param array<Arg|VariadicPlaceholder|ArgPlaceholder> $args Arguments
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(Node $class, array $args = [], array $attributes = []) {
        $this->attributes = $attributes;
        $this->class = $class;
        $this->args = $args;
    }

    public function getSubNodeNames(): array {
        return ['class', 'args'];
    }

    public function getType(): string {
        return 'Expr_New';
    }

    public function getRawArgs(): array {
        return $this->args;
    }
}
