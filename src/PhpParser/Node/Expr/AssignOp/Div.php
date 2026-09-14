<?php declare(strict_types=1);

namespace LaraGram\Probe\PhpParser\Node\Expr\AssignOp;

use LaraGram\Probe\PhpParser\Node\Expr\AssignOp;

class Div extends AssignOp {
    public function getType(): string {
        return 'Expr_AssignOp_Div';
    }
}
