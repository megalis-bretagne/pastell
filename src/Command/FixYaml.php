<?php

declare(strict_types=1);

namespace Pastell\Command;

use Spyc;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class FixYaml extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('dev:fix-yaml')
            ->setDescription('Fix incorrect yaml from previous Pastell version (<4.0)')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'the file to fix'
            )
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run - will not update anything');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('file');
        $array = Spyc::YAMLLoad($file);
        $yaml = Yaml::dump($array, 10);
        file_put_contents($file, $yaml);
        return 0;
    }
}
