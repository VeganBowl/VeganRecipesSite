<?php

namespace Command;

use Cilex\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PreviewCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('preview')
            ->setDescription('Preview the site')
            // ->addArgument('name', InputArgument::OPTIONAL, 'Qui voulez vous saluer??')
            // ->addOption('yell', null, InputOption::VALUE_NONE, 'Si définie, la tâche criera en majuscules')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getContainer();

        $output->writeln('Taboulé');
    }
}
