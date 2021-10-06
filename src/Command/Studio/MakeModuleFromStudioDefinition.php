<?php

namespace Pastell\Command\Studio;

use Pastell\Command\BaseCommand;
use Pastell\Service\TypeDossier\TypeDossierExportFileToModuleService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModuleFromStudioDefinition extends BaseCommand
{
    private $typeDossierExportFileToModuleService;

    public function __construct(TypeDossierExportFileToModuleService $typeDossierExportFileToModuleService)
    {
        $this->typeDossierExportFileToModuleService = $typeDossierExportFileToModuleService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:studio:make-module')
            ->setDescription('Create a module directory from a studio definition file')
            ->addArgument('source', InputArgument::REQUIRED, 'The studio source file in json')
            ->addArgument('target', InputArgument::REQUIRED, 'The target module directory')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $input_filepath = $input->getArgument('source');
        $export_dir_path = $input->getArgument('target');
        $this->typeDossierExportFileToModuleService->export($input_filepath, $export_dir_path);
        return 0;
    }
}
