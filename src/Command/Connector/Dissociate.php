<?php

declare(strict_types=1);

namespace Pastell\Command\Connector;

use ConnecteurDefinitionFiles;
use ConnecteurFactory;
use Exception;
use Pastell\Command\BaseCommand;
use Pastell\Service\Connecteur\ConnecteurAssociationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:connector:dissociate',
    description: 'Dissociates a global connector'
)]
class Dissociate extends BaseCommand
{
    public function __construct(
        private readonly ConnecteurAssociationService $connecteurAssociationService,
        private readonly ConnecteurFactory $connecteurFactory,
        private readonly ConnecteurDefinitionFiles $connecteurDefinitionFiles
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, 'Type of the connector to remove')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run - will not dissociate anything');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        $dryRun = $input->getOption('dry-run');
        $type_exists = \in_array(
            $type,
            $this->connecteurDefinitionFiles->getAllGlobalType(),
            true
        );
        if ($type_exists) {
            try {
                $global_connecteur = $this->connecteurFactory->getGlobalConnecteur($type);
            } catch (Exception $e) {
                $global_connecteur =  false;
            }
            if ($global_connecteur) {
                if ($dryRun) {
                    $this->getIO()->note('Dry run');
                }
                if (
                    $input->isInteractive() &&
                    !$this->getIO()->confirm(
                        sprintf('Are you sure you want to dissociate the `%s` type ?', $type),
                        false
                    )
                ) {
                    return self::FAILURE;
                }
                if ($this->getIO()->isVerbose()) {
                    $this->getIO()->writeln('Dissociating type=' . $type);
                }
                if (!$dryRun) {
                    $this->connecteurAssociationService->deleteConnecteurAssociation(
                        0,
                        $type,
                    );
                }
                $this->getIO()->newLine();
                $this->getIO()->success('Successfully dissociated connector');
            } else {
                $this->getIO()->success('No global connector associated to this type');
            }
        } else {
            $this->getIO()->success('Connector type not found');
        }
        return self::SUCCESS;
    }
}
