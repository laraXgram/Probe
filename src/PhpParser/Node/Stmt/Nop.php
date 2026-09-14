<?php declare(strict_types=1);

namespace LaraGram\Probe\PhpParser\Node\Stmt;

use LaraGram\Probe\PhpParser\Node;

/** Nop/empty statement (;). */
class Nop extends Node\Stmt {
    public function getSubNodeNames(): array {
        return [];
    }

    public function getType(): string {
        return 'Stmt_Nop';
    }
}
