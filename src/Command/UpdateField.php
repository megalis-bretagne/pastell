<?php

namespace Pastell\Command;

use ConnecteurEntiteSQL;
use ConnecteurFactory;
use DocumentEntite;
use DocumentSQL;
use DonneesFormulaireFactory;
use Exception;
use InvalidArgumentException;
use Pastell\Service\SimpleTwigRenderer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function implode;
use function in_array;

class UpdateField extends BaseCommand
{
    public const SCOPE_MODULE = 'module';
    private const SCOPE_CONNECTOR = 'connector';
    private const SCOPE_LIST = [self::SCOPE_MODULE, self::SCOPE_CONNECTOR];

    public function __construct(
        private DocumentSQL $documentSQL,
        private DonneesFormulaireFactory $donneesFormulaireFactory,
        private DocumentEntite $documentEntite,
        private ConnecteurEntiteSQL $connecteurEntiteSql,
        private ConnecteurFactory $connecteurFactory,
        private SimpleTwigRenderer $simpleTwigRenderer,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:update-field')
            ->setDescription('Update field by twig expression for all documents connectors entity or module by type')
            ->addArgument(
                'scope',
                InputArgument::REQUIRED,
                sprintf("Sets the scope of connectors or module (%s)", implode(',', self::SCOPE_LIST))
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
            throw new InvalidArgumentException('Invalid scope');
        }
        $this->getIO()->title(
            sprintf(
                'Start Update field `%s` by twig expression `%s` for all documents %s `%s`',
                $field,
                $twigExpression,
                $scope,
                $type
            )
        );

        if ($scope === self::SCOPE_MODULE) {
            $documents = $this->documentSQL->getAllByType($type);
        } else {
            $documents = $this->connecteurEntiteSql->getAllByConnecteurId($type);
        }
        $documentsNumber = count($documents);
        if ($documentsNumber === 0) {
            $this->getIO()->success(
                sprintf('There is no document %s `%s` to update', $scope, $type)
            );
            return 0;
        }
        if ($dryRun) {
            $this->getIO()->note('Dry run');
        }
        if (
            $input->isInteractive() &&
            !$this->getIO()->confirm(
                sprintf('Are you sure you want to update %d documents %s `%s` ?', $documentsNumber, $scope, $type),
                false
            )
        ) {
            return 1;
        }
        $this->getIO()->progressStart($documentsNumber);
        $this->getIO()->newLine();

        foreach ($documents as $document) {
            if ($scope === self::SCOPE_MODULE) {
                $documentId = $document['id_d'];
                $document['id_e'] = $this->documentEntite->getEntite($documentId)[0]['id_e'];
                $donneesFormulaire = $this->donneesFormulaireFactory->get($documentId);
            } else {
                $documentId = $document['id_ce'];
                $donneesFormulaire = $this->connecteurFactory->getConnecteurConfig($documentId);
            }

            $old_value = $donneesFormulaire->get($field);
            $new_value = $this->simpleTwigRenderer->render(
                $twigExpression,
                $donneesFormulaire
            );
            $this->getIO()->writeln(
                sprintf(
                    'Update id=%s - id_e=%s. Replace field %s value `%s` by `%s`',
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
            $this->getIO()->progressAdvance();
        }

        $this->getIO()->progressFinish();
        $this->getIO()->success('Done');

        return 0;
    }
}
