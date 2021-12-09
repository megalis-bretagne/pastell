<?php

namespace Pastell\Command\Connector;

use ConnecteurEntiteSQL;
use Pastell\Command\BaseCommand;
use Pastell\Service\Connecteur\ConnecteurDeletionService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class DeleteByType extends BaseCommand
{

    /**
     * @var ConnecteurDeletionService
     */
    private $connecteurDeletionService;
    /**
     * @var ConnecteurEntiteSQL
     */
    private $connecteurEntiteSql;

    public function __construct(
        ConnecteurEntiteSQL $connecteurEntiteSql,
        ConnecteurDeletionService $connecteurDeletionService
    ) {
        $this->connecteurEntiteSql = $connecteurEntiteSql;
        $this->connecteurDeletionService = $connecteurDeletionService;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:connector:delete-by-type')
            ->setDescription('Delete all connectors by Type')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of connectors to remove');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $connectors = array_merge(
            $this->connecteurEntiteSql->getAllByConnecteurId($type),
            $this->connecteurEntiteSql->getAllByConnecteurId($type, true)
        );
        $connectorsNumber = count($connectors);
        if (
            $input->isInteractive() &&
            !$this->getIO()->confirm(
                sprintf('Are you sure you want to delete %d `%s` connectors ?', $connectorsNumber, $type),
                false
            )
        ) {
            return 1;
        }
        $this->getIO()->progressStart($connectorsNumber);
        $this->getIO()->newLine();
        foreach ($connectors as $connector) {
            $connectorId = $connector['id_ce'];
            if ($this->getIO()->isVerbose()) {
                $this->getIO()->writeln('Removing id_ce=' . $connectorId . ' - id_e=' . $connector['id_e']);
            }
            $this->connecteurDeletionService->disassociate($connectorId);
            $this->connecteurDeletionService->deleteConnecteur($connectorId);
            $this->getIO()->progressAdvance();
        }

        $this->getIO()->progressFinish();
        $this->getIO()->success('Done');

        return 0;
    }
}
