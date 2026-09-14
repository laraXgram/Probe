<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2026 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LaraGram\Probe\Shell\Command;

use LaraGram\Probe\PhpParser\Node\Arg;
use LaraGram\Probe\PhpParser\Node\Expr\New_;
use LaraGram\Probe\PhpParser\Node\Expr\Variable;
use LaraGram\Probe\PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
use LaraGram\Probe\PhpParser\Node\Scalar\String_;
use LaraGram\Probe\PhpParser\PrettyPrinter\Standard as Printer;
use LaraGram\Probe\Shell\Exception\ThrowUpException;
use LaraGram\Probe\Shell\Input\CodeArgument;
use LaraGram\Console\Input\InputInterface;
use LaraGram\Console\Output\OutputInterface;

/**
 * Throw an exception or error out of the Psy Shell.
 */
class ThrowUpCommand extends Command
{
    private CodeArgumentParser $parser;
    private Printer $printer;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null)
    {
        $this->parser = new CodeArgumentParser();
        $this->printer = new Printer();

        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('throw-up')
            ->setDefinition([
                new CodeArgument('exception', CodeArgument::OPTIONAL, 'Exception or Error to throw.'),
            ])
            ->setDescription('Throw an exception or error out of the Probe.')
            ->setHelp(
                <<<'HELP'
Throws an exception or error out of the current the Probe instance.

By default it throws the most recent exception.

e.g.
<return>>>> throw-up</return>
<return>>>> throw-up $e</return>
<return>>>> throw-up new Exception('WHEEEEEE!')</return>
<return>>>> throw-up "bye!"</return>
HELP
            );
    }

    /**
     * {@inheritdoc}
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws \InvalidArgumentException if there is no exception to throw
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $args = $this->prepareArgs($input->getArgument('exception'));
        $exception = new New_(new FullyQualifiedName(ThrowUpException::class), $args);
        $throwCode = 'throw '.$this->printer->prettyPrintExpr($exception).';';

        $shell = $this->getShell();
        $shell->addCode($throwCode, !$shell->hasCode());

        return 0;
    }

    /**
     * Parse the supplied command argument.
     *
     * If no argument was given, this falls back to `$_e`
     *
     * @throws \InvalidArgumentException if there is no exception to throw
     *
     * @param string|null $code
     *
     * @return Arg[]
     */
    private function prepareArgs(?string $code = null): array
    {
        if (!$code) {
            // Default to last exception if nothing else was supplied
            return [new Arg(new Variable('_e'))];
        }

        $nodes = $this->parser->parse($code);
        if (\count($nodes) !== 1) {
            throw new \InvalidArgumentException('No idea how to throw this');
        }

        $node = $nodes[0];
        $expr = $node->expr;

        $args = [new Arg($expr, false, false, $node->getAttributes())];

        // Allow throwing via a string, e.g. `throw-up "SUP"`
        if ($expr instanceof String_) {
            return [new Arg(new New_(new FullyQualifiedName(\Exception::class), $args))];
        }

        return $args;
    }
}
