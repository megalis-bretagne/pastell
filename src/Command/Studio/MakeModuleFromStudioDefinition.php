<?php

namespace Pastell\Command\Studio;

use Pastell\Command\BaseCommand;
use Pastell\Service\TypeDossier\TypeDossierExportFileToModuleService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MakeModuleFromStudioDefinition extends BaseCommand
{
    private $typeDossierExportFileToModuleService;

    public function __construct(TypeDossierExportFileToModuleService $typeDossierExportFileToModuleService)
    {
        $this->typeDossierExportFileToModuleService = $typeDossierExportFileToModuleService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:studio:make-module')
            ->setDescription('Create a module directory from a studio definition file')
            ->addArgument('source', InputArgument::REQUIRED, 'The studio source file in json')
            ->addArgument('target', InputArgument::REQUIRED, 'The target module directory')
            ->addOption('restriction_pack', 'r', InputOption::VALUE_REQUIRED, "The pack to which the module belongs")
            ->addOption('name', 'name', InputOption::VALUE_REQUIRED, "The name of the module (unmodified by default)")
            ->addOption('id', 'id', InputOption::VALUE_REQUIRED, "The id of the module (unmodified by default)")
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $input_filepath = $input->getArgument('source');
        $export_dir_path = $input->getArgument('target');
        $restriction_pack = $input->getOption('restriction_pack') ?? "";
        $module_name = $input->getOption('name') ?? "";
        $module_id = $input->getOption('id') ?? "";

        $this->getIO()->title("Create module in `$export_dir_path` from studio definition `$input_filepath`");
        $this->typeDossierExportFileToModuleService->export($input_filepath, $export_dir_path, $restriction_pack, $module_id, $module_name);
        $this->getIO()->success('Done');
        return 0;
    }
}
