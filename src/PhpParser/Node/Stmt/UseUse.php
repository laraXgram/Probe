<?php declare(strict_types=1);

namespace LaraGram\Probe\PhpParser\Node\Stmt;

use LaraGram\Probe\PhpParser\Node\UseItem;

require __DIR__ . '/../UseItem.php';

if (false) {
    /**
     * For classmap-authoritative support.
     *
     * @deprecated use \LaraGram\Probe\PhpParser\Node\UseItem instead.
     */
    class UseUse extends UseItem {
    }
}
