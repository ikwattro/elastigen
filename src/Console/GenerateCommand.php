<?php

namespace Ikwattro\ElastiGen\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Ikwattro\ElastiGen\Indexer;
use Ikwattro\ElastiGen\ElastiGenEvents;
use Ikwattro\ElastiGen\GenerationBuilder;

class GenerateCommand extends Command
{
    protected function configure()
    {
        $this->setName('elastigen:generate')
            ->setDescription('Generate data and insert it into ES')
            ->addArgument(
                'host',
                InputArgument::REQUIRED,
                'The ES host'
            )
            ->addArgument(
                'index',
                InputArgument::REQUIRED,
                'The index name'
            )
            ->addArgument(
                'mloc',
                InputArgument::REQUIRED,
                'The path location of the mapping file'
            )
            ->addArgument(
                'ploc',
                InputArgument::REQUIRED,
                'The path to the providers location'
            )
            ->addArgument(
                'iterations',
                InputArgument::OPTIONAL,
                'The number of documents for each type to create'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $mPath = $input->getArgument('mloc');
        $pPath = $input->getArgument('ploc');
        $this->checkFile($mPath);
        $this->checkFile($pPath);

        $indexer = new Indexer($input->getArgument('host'), $input->getArgument('index'));
        $ev = new EventDispatcher();
        $ev->addListener(ElastiGenEvents::DOCUMENT, array($indexer, 'onDocument'));
        $generator = new GenerationBuilder($ev);
        $it = null !== $input->getArgument('iterations') ? $input->getArgument('iterations') : PHP_INT_MAX;

        $mapping = json_decode(file_get_contents($mPath), true);
        $providers = json_decode(file_get_contents($pPath), true);

        $newMap = $generator->map($mapping, $providers);
        $output->writeln("Starting generation");
        $generator->generate($newMap, $it);
        $output->writeln(sprintf('%d generations completed', $it));
    }

    private function checkFile($path)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf('Unable to find file %s', $path));
        }
    }
}