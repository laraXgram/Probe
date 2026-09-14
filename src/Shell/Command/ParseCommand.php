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

use LaraGram\Probe\PhpParser\Error as PhpParserError;
use LaraGram\Probe\PhpParser\Node;
use LaraGram\Probe\PhpParser\Parser;
use LaraGram\Probe\Shell\Context;
use LaraGram\Probe\Shell\ContextAware;
use LaraGram\Probe\Shell\Input\CodeArgument;
use LaraGram\Probe\Shell\ParserFactory;
use LaraGram\Probe\Shell\VarDumper\Presenter;
use LaraGram\Probe\Shell\VarDumper\PresenterAware;
use LaraGram\Console\Input\InputInterface;
use LaraGram\Console\Input\InputOption;
use LaraGram\Console\Output\OutputInterface;
use LaraGram\Http\VarDumper\Caster\Caster;

/**
 * Parse PHP code and show the abstract syntax tree.
 */
class ParseCommand extends Command implements ContextAware, PresenterAware
{
    protected Context $context;
    private Presenter $presenter;
    private Parser $parser;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null)
    {
        $this->parser = (new ParserFactory())->createParser();

        parent::__construct($name);
    }

    /**
     * ContextAware interface.
     *
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * PresenterAware interface.
     *
     * @param Presenter $presenter
     */
    public function setPresenter(Presenter $presenter)
    {
        $this->presenter = clone $presenter;
        $this->presenter->addCasters([
            Node::class => function (Node $node, array $a) {
                $a = [
                    Caster::PREFIX_VIRTUAL.'type'       => $node->getType(),
                    Caster::PREFIX_VIRTUAL.'attributes' => $node->getAttributes(),
                ];

                foreach ($node->getSubNodeNames() as $name) {
                    $a[Caster::PREFIX_VIRTUAL.$name] = $node->$name;
                }

                return $a;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('parse')
            ->setDefinition([
                new CodeArgument('code', CodeArgument::REQUIRED, 'PHP code to parse.'),
                new InputOption('depth', '', InputOption::VALUE_REQUIRED, 'Depth to parse.', 10),
            ])
            ->setDescription('Parse PHP code and show the abstract syntax tree.')
            ->setHelp(
                <<<'HELP'
Parse PHP code and show the abstract syntax tree.

This command is used in the development of Probe. Given a string of PHP code,
it pretty-prints the PHP Parser parse tree.

See https://github.com/nikic/PHP-Parser

It prolly won't be super useful for most of you, but it's here if you want to play.
HELP
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $code = $input->getArgument('code');
        $depth = $input->getOption('depth');

        if (!\preg_match('/^\s*<\\?/', $code)) {
            $code = '<?php '.$code;
        }

        try {
            $nodes = $this->parser->parse($code);
        } catch (PhpParserError $e) {
            if ($this->parseErrorIsEOF($e)) {
                $nodes = $this->parser->parse($code.';');
            } else {
                throw $e;
            }
        }

        $this->shellOutput($output)->page($this->presenter->present($nodes, $depth, Presenter::RAW), OutputInterface::OUTPUT_RAW);

        $this->context->setReturnValue($nodes);

        return 0;
    }

    private function parseErrorIsEOF(PhpParserError $e): bool
    {
        $msg = $e->getRawMessage();

        return ($msg === 'Unexpected token EOF') || (\strpos($msg, 'Syntax error, unexpected EOF') !== false);
    }
}
