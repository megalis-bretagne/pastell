<?php

namespace Pastell\Command\Connector;

use ConnecteurEntiteSQL;
use Pastell\Command\BaseCommand;
use Pastell\Service\Connecteur\ConnecteurAssociationService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Dissociate extends BaseCommand
{
    /**
     * @var ConnecteurAssociationService
     */
    private ConnecteurAssociationService $connecteurAssociationService;
    /**
     * @var ConnecteurEntiteSQL
     */
    private ConnecteurEntiteSQL $connecteurEntiteSql;

    public function __construct(
        ConnecteurEntiteSQL $connecteurEntiteSql,
        ConnecteurAssociationService $connecteurAssociationService
    ) {
        $this->connecteurEntiteSql = $connecteurEntiteSql;
        $this->connecteurAssociationService = $connecteurAssociationService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:connector:dissociate')
            ->setDescription('Dissociate a global connector')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of the connector to remove')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run - will not dissociate anything')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        $dryRun = $input->getOption('dry-run');

        $connectorExists = count($this->connecteurEntiteSql->getAllByConnecteurId($type, true)) !== 0;

        if ($connectorExists) {
            if ($dryRun) {
                $this->getIO()->note('Dry run');
            }
            if (
                $input->isInteractive() &&
                !$this->getIO()->confirm(
                    sprintf('Are you sure you want to dissociate the `%s` connector ?', $type),
                    false
                )
            ) {
                return 1;
            }
            $this->getIO()->progressStart(1);
            if ($this->getIO()->isVerbose()) {
                $this->getIO()->writeln('Dissociating type=' . $type);
            }
            if (!$dryRun) {
                $this->connecteurAssociationService->deleteConnecteurAssociation(
                    0,
                    $type,
                    0,
                    '',
                    0
                );
            }
            $this->getIO()->newLine();
            $this->getIO()->progressFinish();
            $this->getIO()->success('Successfully dissociated connector');
        } else {
            $this->getIO()->success('Global connector not found');
        }
        return 0;
    }
}
