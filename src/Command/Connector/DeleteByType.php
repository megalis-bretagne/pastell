<?php

namespace Pastell\Command\Connector;

use ConnecteurEntiteSQL;
use Pastell\Command\BaseCommand;
use Pastell\Service\Connecteur\ConnecteurDeletionService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    private const SCOPE_ALL = 'all';
    private const SCOPE_GLOBAL = 'global';
    private const SCOPE_ENTITY = 'entity';
    private const SCOPE_LIST = [self::SCOPE_ALL, self::SCOPE_GLOBAL, self::SCOPE_ENTITY];

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
            ->setDescription('Delete all connectors by type')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of connectors to remove')
            ->addArgument(
                'scope',
                InputArgument::OPTIONAL,
                sprintf("Sets the scope of connectors to be removed (%s)", \implode(',', self::SCOPE_LIST)),
                self::SCOPE_ALL
            )
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run - will not delete anything')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getArgument('type');
        $scope = $input->getArgument('scope');
        $dryRun = $input->getOption('dry-run');

        if (!\in_array($scope, self::SCOPE_LIST, true)) {
            throw new \InvalidArgumentException('Invalid scope');
        }
        if ($scope === self::SCOPE_GLOBAL) {
            $connectors = $this->connecteurEntiteSql->getAllByConnecteurId($type, true);
        } elseif ($scope === self::SCOPE_ENTITY) {
            $connectors = $this->connecteurEntiteSql->getAllByConnecteurId($type);
        } else {
            $connectors = array_merge(
                $this->connecteurEntiteSql->getAllByConnecteurId($type),
                $this->connecteurEntiteSql->getAllByConnecteurId($type, true)
            );
        }
        $connectorsNumber = count($connectors);
        if ($connectorsNumber === 0) {
            $this->getIO()->success('There is no connectors to delete');
            return 0;
        }
        if ($dryRun) {
            $this->getIO()->note('Dry run');
        }
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
            if (!$dryRun) {
                $this->connecteurDeletionService->disassociate($connectorId);
                $this->connecteurDeletionService->deleteConnecteur($connectorId);
            }
            $this->getIO()->progressAdvance();
        }

        $this->getIO()->progressFinish();
        $this->getIO()->success('Done');

        return 0;
    }
}
