<?php declare(strict_types=1);

namespace LaraGram\Probe\PhpParser\Lexer\TokenEmulator;

use LaraGram\Probe\PhpParser\PhpVersion;

final class PropertyTokenEmulator extends KeywordEmulator {
    public function getPhpVersion(): PhpVersion {
        return PhpVersion::fromComponents(8, 4);
    }

    public function getKeywordString(): string {
        return '__property__';
    }

    public function getKeywordToken(): int {
        return \T_PROPERTY_C;
    }
}
