<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\Command\Config;

use LaraGram\Console\Input\InputArgument;
use LaraGram\Console\Input\InputInterface;
use LaraGram\Console\Output\OutputInterface;

/**
 * Print the current value for a runtime-configurable PsySH setting.
 */
class ConfigGetCommand extends AbstractConfigCommand
{
    protected function configure(): void
    {
        $this
            ->setName('config-get')
            ->setDefinition([
                new InputArgument('key', InputArgument::OPTIONAL, 'Runtime-configurable option to inspect.'),
            ])
            ->setDescription('Print the current value for one runtime-configurable Probe setting.');
    }

    public function asText(): string
    {
        return \implode("\n", [
            '<comment>Usage:</comment>',
            ' config get \\<key>',
            '',
            '<comment>Help:</comment>',
            ' Print the current value for one runtime-configurable Probe setting.',
            '',
            '<comment>Examples:</comment>',
            ' <return>>>> config get verbosity</return>',
            ' <return>>>> config get theme</return>',
            '',
            '<comment>Supported Options:</comment>',
            ' '.$this->formatOptionNames($this->getOptionNames()),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getArgument('key');
        if ($key === null) {
            throw new \InvalidArgumentException('Please specify a runtime-configurable option to inspect.');
        }

        $option = $this->getOption($key);
        if ($option === null) {
            $output->writeln(\sprintf('<error>%s</error>', $this->unsupportedMessage((string) $key)));

            return 1;
        }

        $output->writeln($this->formatValue($option['getter']()));

        return 0;
    }
}
