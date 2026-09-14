<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell;

use LaraGram\Probe\Shell\Exception\BreakException;
use LaraGram\Probe\Shell\Exception\InvalidManualException;
use LaraGram\Probe\Shell\ExecutionLoop\ProcessForker;
use LaraGram\Probe\Shell\Util\DependencyChecker;
use LaraGram\Console\Formatter\OutputFormatter;
use LaraGram\Console\Input\ArgvInput;
use LaraGram\Console\Input\InputArgument;
use LaraGram\Console\Input\InputDefinition;
use LaraGram\Console\Input\InputOption;
use LaraGram\Console\Output\OutputInterface;

if (!\function_exists('LaraGram\\Probe\\Shell\\sh')) {
    /**
     * Command to return the eval-able code to startup PsySH.
     *
     *     eval(\LaraGram\Probe\Shell\sh());
     */
    function sh(): string
    {
        if (\PHP_VERSION_ID < 80000) {
            return '\extract(\LaraGram\Probe\Shell\debug(\get_defined_vars(), isset($this) ? $this : @\get_called_class()));';
        }

        return <<<'EOS'
if (isset($this)) {
    \extract(\LaraGram\Probe\Shell\debug(\get_defined_vars(), $this));
} else {
    try {
        static::class;
        \extract(\LaraGram\Probe\Shell\debug(\get_defined_vars(), static::class));
    } catch (\Error $e) {
        \extract(\LaraGram\Probe\Shell\debug(\get_defined_vars()));
    }
}
EOS;
    }
}

if (!\function_exists('LaraGram\\Probe\\Shell\\debug')) {
    /**
     * Invoke a Psy Shell from the current context.
     *
     * For example:
     *
     *     foreach ($items as $item) {
     *         \LaraGram\Probe\Shell\debug(get_defined_vars());
     *     }
     *
     * If you would like your shell interaction to affect the state of the
     * current context, you can extract() the values returned from this call:
     *
     *     foreach ($items as $item) {
     *         extract(\LaraGram\Probe\Shell\debug(get_defined_vars()));
     *         var_dump($item); // will be whatever you set $item to in Psy Shell
     *     }
     *
     * Optionally, supply an object as the `$bindTo` parameter. This determines
     * the value `$this` will have in the shell, and sets up class scope so that
     * private and protected members are accessible:
     *
     *     class Foo {
     *         function bar() {
     *             \LaraGram\Probe\Shell\debug(get_defined_vars(), $this);
     *         }
     *     }
     *
     * For the static equivalent, pass a class name as the `$bindTo` parameter.
     * This makes `self` work in the shell, and sets up static scope so that
     * private and protected static members are accessible:
     *
     *     class Foo {
     *         static function bar() {
     *             \LaraGram\Probe\Shell\debug(get_defined_vars(), get_called_class());
     *         }
     *     }
     *
     * @param array         $vars   Scope variables from the calling context (default: [])
     * @param object|string $bindTo Bound object ($this) or class (self) value for the shell
     *
     * @return array Scope variables from the debugger session
     */
    function debug(array $vars = [], $bindTo = null): array
    {
        echo \PHP_EOL;

        $sh = new Shell();
        $sh->setScopeVariables($vars);

        // Show a couple of lines of call context for the debug session.
        //
        // @todo come up with a better way of doing this which doesn't involve injecting input :-P
        if ($sh->has('whereami')) {
            $sh->addInput('whereami -n2', true);
        }

        if (\is_string($bindTo)) {
            $sh->setBoundClass($bindTo);
        } elseif ($bindTo !== null) {
            $sh->setBoundObject($bindTo);
        }

        $sh->run();

        return $sh->getScopeVariables(false);
    }
}

if (!\function_exists('LaraGram\\Probe\\Shell\\info')) {
    /**
     * Get a bunch of debugging info about the current PsySH environment and
     * configuration.
     *
     * If a Configuration param is passed, that configuration is stored and
     * used for the current shell session, and no debugging info is returned.
     *
     * @param Configuration|null $config
     *
     * @return array|null
     */
    function info(?Configuration $config = null)
    {
        static $lastConfig;
        if ($config !== null) {
            $lastConfig = $config;

            return null;
        }

        $config = $lastConfig ?: new Configuration();
        $configEnv = (isset($_SERVER['PROBE_CONFIG']) && $_SERVER['PROBE_CONFIG']) ? $_SERVER['PROBE_CONFIG'] : false;
        if ($configEnv === false && \PHP_SAPI === 'cli-server') {
            $configEnv = \getenv('PROBE_CONFIG');
        }

        $shellInfo = [
            'Probe version' => Shell::VERSION,
        ];

        $core = [
            'PHP version'         => \PHP_VERSION,
            'OS'                  => \PHP_OS,
            'default includes'    => $config->getDefaultIncludes(),
            'require semicolons'  => $config->requireSemicolons(),
            'strict types'        => $config->strictTypes(),
            'error logging level' => $config->errorLoggingLevel(),
            'config file'         => [
                'default config file' => ConfigPaths::prettyPath($config->getConfigFile()),
                'local config file'   => ConfigPaths::prettyPath($config->getLocalConfigFile()),
                'PROBE_CONFIG env'    => ConfigPaths::prettyPath($configEnv),
            ],
            // 'config dir'  => $config->getConfigDir(),
            // 'data dir'    => $config->getDataDir(),
            // 'runtime dir' => $config->getRuntimeDir(),
        ];

        $input = [
            'interactive mode'  => $config->interactiveMode(),
            'input interactive' => $config->getInputInteractive(),
            'bracketed paste'   => $config->useBracketedPaste(),
            'yolo'              => $config->yolo(),
        ];

        $readlineService = $config->getReadline();
        $interactiveReadline = $readlineService instanceof Readline\InteractiveReadlineInterface;

        $readline = [
            'readline available' => $config->hasReadline(),
            'readline enabled'   => $config->useReadline(),
            'readline service'   => \get_class($readlineService),
        ];

        // Only show system readline library info when actually using it
        if ($config->hasReadline() && !$interactiveReadline) {
            $info = \readline_info();

            if (isset($info['library_version'])) {
                $readline['readline library'] = $info['library_version'];
            }

            if (isset($info['readline_name']) && $info['readline_name'] !== '') {
                $readline['readline name'] = $info['readline_name'];
            }
        }

        $readline['interactive readline requested'] = $config->useExperimentalReadline();

        if ($interactiveReadline) {
            $readline['syntax highlighting'] = $config->useSyntaxHighlighting();
        }

        // Show supported diagnostic when requested but not active
        if (!$interactiveReadline) {
            $readline['interactive readline supported'] = Readline\InteractiveReadline::isSupported();
        }

        $output = [
            'color mode'       => $config->colorMode(),
            'output decorated' => $config->getOutputDecorated(),
            'output verbosity' => $config->verbosity(),
            'output pager'     => $config->getPager(),
        ];

        $theme = $config->theme();
        $output['theme'] = $theme->getName() ?? [
            // @todo show styles (but only if they're different than default?)
            'compact'      => $theme->compact(),
            'prompt'       => $theme->prompt(),
            'bufferPrompt' => $theme->bufferPrompt(),
            'replayPrompt' => $theme->replayPrompt(),
            'returnValue'  => $theme->returnValue(),
        ];

        $pcntl = [
            'pcntl available' => DependencyChecker::functionsAvailable(ProcessForker::PCNTL_FUNCTIONS),
            'posix available' => DependencyChecker::functionsAvailable(ProcessForker::POSIX_FUNCTIONS),
        ];

        if ($disabledPcntl = DependencyChecker::functionsDisabled(ProcessForker::PCNTL_FUNCTIONS)) {
            $pcntl['disabled pcntl functions'] = $disabledPcntl;
        }

        if ($disabledPosix = DependencyChecker::functionsDisabled(ProcessForker::POSIX_FUNCTIONS)) {
            $pcntl['disabled posix functions'] = $disabledPosix;
        }

        $pcntl['use pcntl'] = $config->usePcntl();

        $history = [
            'history file'     => ConfigPaths::prettyPath($config->getHistoryFile()),
            'history format'   => $interactiveReadline ? 'jsonl' : 'plain text',
            'history size'     => $config->getHistorySize(),
            'erase duplicates' => $config->getEraseDuplicates(),
        ];

        $manualDbFile = $config->getManualDbFile();
        $manual = null;
        $manualError = null;

        try {
            $manual = $config->getManual();
        } catch (InvalidManualException $e) {
            $manualError = $e->getMessage();
        }

        // If we have a manual but no db file path, it's bundled in the PHAR
        if ($manual && !$manualDbFile && \Phar::running(false)) {
            $docs = [
                'manual db file' => '<bundled>',
            ];
        } else {
            $docs = [
                'manual db file' => ConfigPaths::prettyPath($manualDbFile),
            ];
        }

        if ($manualError) {
            $docs['manual error'] = $manualError;
        } elseif ($manual) {
            $meta = $manual->getMeta();

            foreach ($meta as $key => $val) {
                switch ($key) {
                    case 'built_at':
                        $d = new \DateTime('@'.$val);
                        $val = $d->format(\DateTime::RFC2822);
                        break;
                }
                $key = 'manual '.\str_replace('_', ' ', $key);
                $docs[$key] = $val;
            }
        }

        $completionIntegration = 'disabled';
        if ($config->useTabCompletion()) {
            $completionIntegration = $interactiveReadline ? 'interactive readline' : 'legacy readline shim';
        }

        $autocomplete = [
            'tab completion enabled'  => $config->useTabCompletion(),
            'completion integration'  => $completionIntegration,
            'inline suggestions'      => $interactiveReadline && $config->useSuggestions(),
        ];

        $warmers = $config->getAutoloadWarmers();
        $autoload = [
            'autoload warming enabled' => !empty($warmers),
            'warmers configured'       => \count($warmers),
        ];

        if (!empty($warmers)) {
            $autoload['warmer types'] = \array_map('get_class', $warmers);

            // Add extended info for ComposerAutoloadWarmer
            foreach ($warmers as $warmer) {
                if ($warmer instanceof TabCompletion\AutoloadWarmer\ComposerAutoloadWarmer) {
                    try {
                        $autoload['composer warmer config'] = [
                            'include vendor' => Sudo::fetchProperty($warmer, 'includeVendor'),
                            'include tests'  => Sudo::fetchProperty($warmer, 'includeTests'),
                            'vendor dir'     => Sudo::fetchProperty($warmer, 'vendorDir'),
                            'phar prefix'    => Sudo::fetchProperty($warmer, 'pharPrefix'),
                        ];

                        $includeNamespaces = Sudo::fetchProperty($warmer, 'includeNamespaces');
                        $excludeNamespaces = Sudo::fetchProperty($warmer, 'excludeNamespaces');
                        $includeVendorNamespaces = Sudo::fetchProperty($warmer, 'includeVendorNamespaces');
                        $excludeVendorNamespaces = Sudo::fetchProperty($warmer, 'excludeVendorNamespaces');

                        if (!empty($includeNamespaces)) {
                            $autoload['composer warmer config']['include namespaces'] = $includeNamespaces;
                        }
                        if (!empty($excludeNamespaces)) {
                            $autoload['composer warmer config']['exclude namespaces'] = $excludeNamespaces;
                        }
                        if (!empty($includeVendorNamespaces)) {
                            $autoload['composer warmer config']['include vendor namespaces'] = $includeVendorNamespaces;
                        }
                        if (!empty($excludeVendorNamespaces)) {
                            $autoload['composer warmer config']['exclude vendor namespaces'] = $excludeVendorNamespaces;
                        }
                    } catch (\ReflectionException $e) {
                        // shrug
                    }
                    break; // Only show info for the first ComposerAutoloadWarmer
                }
            }
        }

        $implicitUse = [];
        $implicitUseConfig = $config->getImplicitUse();
        if (\is_array($implicitUseConfig)) {
            if (!empty($implicitUseConfig['includeNamespaces'])) {
                $implicitUse['include namespaces'] = $implicitUseConfig['includeNamespaces'];
            }
            if (!empty($implicitUseConfig['excludeNamespaces'])) {
                $implicitUse['exclude namespaces'] = $implicitUseConfig['excludeNamespaces'];
            }
        }
        if (empty($implicitUse)) {
            $implicitUse = false;
        }

        // Shenanigans, but totally justified.
        try {
            if ($shell = Sudo::fetchProperty($config, 'shell')) {
                $shellClass = \get_class($shell);
                if ($shellClass !== 'LaraGram\\Probe\\Shell\\Shell') {
                    $shellInfo = [
                        'Probe version' => $shell::VERSION,
                        'Shell class'   => $shellClass,
                    ];
                }

                try {
                    $core['loop listeners'] = \array_map('get_class', Sudo::fetchProperty($shell, 'loopListeners'));
                } catch (\ReflectionException $e) {
                    // shrug
                }

                $core['commands'] = \array_map('get_class', $shell->all());

                try {
                    $autocomplete['custom legacy matchers'] = \array_map('get_class', Sudo::fetchProperty($shell, 'matchers'));
                } catch (\ReflectionException $e) {
                    // shrug
                }

                try {
                    $completionEngine = Sudo::fetchProperty($shell, 'completionEngine');
                    if ($completionEngine !== null) {
                        $autocomplete['completion sources'] = \array_map('get_class', Sudo::fetchProperty($completionEngine, 'sources'));
                        $autocomplete['completion refiners'] = \array_map('get_class', Sudo::fetchProperty($completionEngine, 'refiners'));
                    }
                } catch (\ReflectionException $e) {
                    // shrug
                }

                try {
                    $pendingCompletionSources = Sudo::fetchProperty($shell, 'pendingCompletionSources');
                    if (!empty($pendingCompletionSources)) {
                        $autocomplete['pending completion sources'] = \array_map('get_class', $pendingCompletionSources);
                    }
                } catch (\ReflectionException $e) {
                    // shrug
                }
            }
        } catch (\ReflectionException $e) {
            // shrug
        }

        // @todo Show Presenter / custom casters.

        return \array_merge(
            $shellInfo,
            $core,
            \compact(
                'pcntl',
                'input',
                'readline',
                'output',
                'history',
                'docs',
                'autocomplete',
                'autoload'
            ),
            [
                'implicit use' => $implicitUse,
            ],
        );
    }
}

