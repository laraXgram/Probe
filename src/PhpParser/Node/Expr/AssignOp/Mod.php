<?php declare(strict_types=1);

namespace LaraGram\Probe\PhpParser\Node\Expr\AssignOp;

use LaraGram\Probe\PhpParser\Node\Expr\AssignOp;

class Mod extends AssignOp {
    public function getType(): string {
        return 'Expr_AssignOp_Mod';
    }
}
