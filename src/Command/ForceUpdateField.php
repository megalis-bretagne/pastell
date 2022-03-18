<?php

namespace Pastell\Command;

use ConnecteurEntiteSQL;
use ConnecteurFactory;
use DocumentSQL;
use DonneesFormulaireFactory;
use Exception;
use InvalidArgumentException;
use Journal;
use NotFoundException;
use Pastell\Service\SimpleTwigRenderer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UnrecoverableException;

use function implode;
use function in_array;

class ForceUpdateField extends BaseCommand
{
    public const SCOPE_MODULE = 'module';
    private const SCOPE_CONNECTOR = 'connector';
    private const SCOPE_LIST = [self::SCOPE_MODULE, self::SCOPE_CONNECTOR];

    public function __construct(
        private DocumentSQL $documentSQL,
        private DonneesFormulaireFactory $donneesFormulaireFactory,
        private ConnecteurEntiteSQL $connecteurEntiteSql,
        private ConnecteurFactory $connecteurFactory,
        private SimpleTwigRenderer $simpleTwigRenderer,
        private Journal $journal,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:force-update-field')
            ->setDescription('Update field by twig expression for all connectors entity or module by type')
            ->addArgument(
                'scope',
                InputArgument::REQUIRED,
                sprintf("Sets the scope of connectors or module (%s)", implode(', ', self::SCOPE_LIST))
            )
            ->addArgument('type', InputArgument::REQUIRED, 'Type of connectors entity or module to update')
            ->addArgument('field', InputArgument::REQUIRED, 'Field to update')
            ->addArgument('twigExpression', InputArgument::REQUIRED, 'Twig expression for update field value')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run - will not update anything');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scope = $input->getArgument('scope');
        $type = $input->getArgument('type');
        $field = $input->getArgument('field');
        $twigExpression = $input->getArgument('twigExpression');
        $dryRun = $input->getOption('dry-run');

        if (!in_array($scope, self::SCOPE_LIST, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Scope `%s` is invalid. It needs to be in (%s)',
                    $scope,
                    implode(', ', self::SCOPE_LIST)
                )
            );
        }

        $this->getIO()->title(
            sprintf(
                'Start Update field `%s` by twig expression `%s` for all %s `%s`',
                $field,
                $twigExpression,
                $scope,
                $type
            )
        );

        if ($scope === self::SCOPE_MODULE) {
            $scopeType = 'dossiers';
            $documents = $this->documentSQL->getAllIdByType($type);
        } else {
            $scopeType = 'configuration';
            $documents = $this->connecteurEntiteSql->getAllByConnecteurId($type);
        }
        $documentsNumber = count($documents);
        if ($documentsNumber === 0) {
            $this->getIO()->success(
                sprintf('There is no %s %s `%s` to update', $scopeType, $scope, $type)
            );
            return 0;
        }
        if ($dryRun) {
            $this->getIO()->note('Dry run');
        } elseif (
            $input->isInteractive() &&
            !$this->getIO()->confirm(
                sprintf('Are you sure you want to update %d %s %s `%s` ?', $documentsNumber, $scopeType, $scope, $type),
                false
            )
        ) {
            return 1;
        }
        $this->getIO()->progressStart($documentsNumber);
        $this->getIO()->newLine();

        foreach ($documents as $document) {
            $this->updateField($document, $scope, $field, $twigExpression, $dryRun);
            $this->getIO()->progressAdvance();
        }

        if (!$dryRun) {
            $this->journal->addSQL(
                Journal::COMMANDE,
                0,
                0,
                '',
                '',
                sprintf(
                    '`app:force-update-field` Update field `%s` by twig expression `%s` for %s %s %s `%s`',
                    $field,
                    $twigExpression,
                    $documentsNumber,
                    $scopeType,
                    $scope,
                    $type
                )
            );
        }

        $this->getIO()->progressFinish();
        $this->getIO()->success('Done');

        return 0;
    }

    /**
     * @throws UnrecoverableException
     * @throws NotFoundException
     * @throws Exception
     */
    private function updateField(
        array $document,
        string $scope,
        string $field,
        string $twigExpression,
        string $dryRun
    ): void {
        if ($scope === self::SCOPE_MODULE) {
            $scopeId = 'id_d';
            $documentId = $document[$scopeId];
            $donneesFormulaire = $this->donneesFormulaireFactory->get($documentId);
        } else {
            $scopeId = 'id_ce';
            $documentId = $document[$scopeId];
            $donneesFormulaire = $this->connecteurFactory->getConnecteurConfig($documentId);
        }

        $old_value = $donneesFormulaire->get($field);
        $new_value = $this->simpleTwigRenderer->render(
            $twigExpression,
            $donneesFormulaire
        );
        $this->getIO()->writeln(
            sprintf(
                'Update %s=%s - id_e=%s. Replace field %s value `%s` by `%s`',
                $scopeId,
                $documentId,
                $document['id_e'],
                $field,
                $old_value,
                $new_value
            )
        );
        if (!$dryRun) {
            $donneesFormulaire->setData($field, $new_value);
        }
    }
}
