<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\VarDumper;

use LaraGram\Http\VarDumper\Caster\Caster;
use LaraGram\Http\VarDumper\Cloner\Data;
use LaraGram\Http\VarDumper\Cloner\Stub;
use LaraGram\Http\VarDumper\Cloner\VarCloner;

/**
 * A PsySH-specialized VarCloner.
 */
class Cloner extends VarCloner
{
    private int $filter = 0;

    /**
     * {@inheritdoc}
     */
    public function cloneVar($var, $filter = 0): Data
    {
        $this->filter = $filter;

        return parent::cloneVar($var, $filter);
    }

    /**
     * {@inheritdoc}
     */
    protected function castResource(Stub $stub, $isNested): array
    {
        return Caster::EXCLUDE_VERBOSE & $this->filter ? [] : parent::castResource($stub, $isNested);
    }
}
