<?php

namespace LaraGram\Probe\Console;

use LaraGram\Console\Command;
use LaraGram\Support\Env;
use LaraGram\Probe\ClassAliasAutoloader;
use LaraGram\Probe\Shell\Configuration;
use LaraGram\Probe\Shell\Shell;
use LaraGram\Console\Input\InputArgument;
use LaraGram\Console\Input\InputOption;
use Throwable;

class ProbeCommand extends Command
{
    /**
     * Commander commands to include in the probe shell.
     *
     * @var array
     */
    protected $commandWhitelist = [
        'clear-compiled', 'down', 'env', 'inspire', 'migrate', 'migrate:install', 'optimize', 'up',
    ];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'probe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Interact with your application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->getApplication()->setCatchExceptions(false);

        $config = Configuration::fromInput($this->input);

        $appConfig = $this->getLaraGram()->make('config');
        $config->setTrustProject($appConfig->get('probe.trust_project'));

        $config->getPresenter()->addCasters(
            $this->getCasters()
        );

        if ($this->option('execute')) {
            $config->setRawOutput(true);
        }

        $shell = new Shell($config);
        $shell->addCommands($this->getCommands());
        $shell->setIncludes($this->argument('include'));

        $path = Env::get('COMPOSER_VENDOR_DIR', $this->getLaraGram()->basePath().DIRECTORY_SEPARATOR.'vendor');

        $path .= '/composer/autoload_classmap.php';

        $loader = ClassAliasAutoloader::register(
            $shell, $path, $appConfig->get('probe.alias', []), $appConfig->get('probe.dont_alias', [])
        );

        if ($code = $this->option('execute')) {
            try {
                $shell->setOutput($this->output);
                $shell->execute($code, true);
            } catch (Throwable $e) {
                $shell->writeException($e);

                return 1;
            } finally {
                $loader->unregister();
            }

            return 0;
        }

        try {
            return $shell->run();
        } finally {
            $loader->unregister();
        }
    }

    /**
     * Get commander commands to pass through to PsySH.
     *
     * @return array
     */
    protected function getCommands()
    {
        $commands = [];

        foreach ($this->getApplication()->all() as $name => $command) {
            if (in_array($name, $this->commandWhitelist)) {
                $commands[] = $command;
            }
        }

        $config = $this->getLaraGram()->make('config');

        foreach ($config->get('probe.commands', []) as $command) {
            $commands[] = $this->getApplication()->add(
                $this->getLaraGram()->make($command)
            );
        }

        return $commands;
    }

    /**
     * Get an array of LaraGram tailored casters.
     *
     * @return array
     */
    protected function getCasters()
    {
        $casters = [
            'LaraGram\Support\Collection' => 'LaraGram\Probe\ProbeCaster::castCollection',
            'LaraGram\Support\HtmlString' => 'LaraGram\Probe\ProbeCaster::castHtmlString',
            'LaraGram\Support\Stringable' => 'LaraGram\Probe\ProbeCaster::castStringable',
        ];

        if (class_exists('LaraGram\Database\Eloquent\Model')) {
            $casters['LaraGram\Database\Eloquent\Model'] = 'LaraGram\Probe\ProbeCaster::castModel';
        }

        if (class_exists('LaraGram\Process\ProcessResult')) {
            $casters['LaraGram\Process\ProcessResult'] = 'LaraGram\Probe\ProbeCaster::castProcessResult';
        }

        if (class_exists('LaraGram\Foundation\Application')) {
            $casters['LaraGram\Foundation\Application'] = 'LaraGram\Probe\ProbeCaster::castApplication';
        }

        $config = $this->getLaraGram()->make('config');

        return array_merge($casters, (array) $config->get('probe.casters', []));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['include', InputArgument::IS_ARRAY, 'Include file(s) before starting probe'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['execute', null, InputOption::VALUE_OPTIONAL, 'Execute the given code using Probe'],
        ];
    }
}
