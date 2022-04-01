<?php

namespace Pastell\Command\Module;

use ActionExecutorFactory;
use DocumentEntite;
use DocumentSQL;
use DonneesFormulaireFactory;
use Exception;
use Pastell\Command\BaseCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HeliosAddExtractionPesAller extends BaseCommand
{
    private const MODULE_LIST = ['helios-automatique', 'helios-generique'];
    private const FIELD_EXTRACT_ID_COLL = 'id_coll'; //to check if the extraction has already been done
    private const FIELD_ETAT_ACK = 'etat_ack'; //field whose value must be kept
    private const ACTION_EXTRACT = 'fichier_pes_change'; //action on the document to extract fichier_pes

    /**
     * @var DocumentSQL
     */
    private $documentSQL;

    /**
     * @var DocumentEntite
     */
    private $documentEntite;

    /**
     * @var DonneesFormulaireFactory
     */
    private $donneesFormulaireFactory;

    /**
     * @var ActionExecutorFactory
     */
    private $actionExecutorFactory;

    public function __construct(
        DocumentSQL $documentSQL,
        DocumentEntite $documentEntite,
        DonneesFormulaireFactory $donneesFormulaireFactory,
        ActionExecutorFactory $actionExecutorFactory
    ) {
        $this->documentSQL = $documentSQL;
        $this->documentEntite = $documentEntite;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
        $this->actionExecutorFactory = $actionExecutorFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:module:helios-add-extraction-pes-aller')
            ->setDescription(
                'Extract informations fichier_pes for source module helios-generique or helios-automatique'
            )
            ->addArgument(
                'source',
                InputOption::VALUE_REQUIRED,
                sprintf("Sets the source module (%s)", implode(', ', self::MODULE_LIST))
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Dry run - will not update anything'
            )
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = $input->getArgument('source');
        $dryRun = $input->getOption('dry-run');

        if (!in_array($source, self::MODULE_LIST, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Source `%s` is invalid. It needs to be in (%s)',
                    $source,
                    implode(', ', self::MODULE_LIST)
                )
            );
        }
        if (!$source) {
            throw new InvalidArgumentException("Missing source module helios");
        }
        $this->getIO()->title(
            "Start extract informations fichier_pes for `$source`"
        );

        $arrayDocuments = [];
        foreach ($this->documentSQL->getAllByType($source) as $document_info) {
            $id_d = $document_info['id_d'];
            $donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);
            if (!$donneesFormulaire->get(self::FIELD_EXTRACT_ID_COLL)) {
                $arrayDocuments[] = $id_d;
            }
        }

        $numberOfDocument = count($arrayDocuments);
        if ($numberOfDocument === 0) {
            $this->getIO()->success(
                sprintf('There is no `%s` to extract fichier_pes', $source)
            );
            return 0;
        }
        if ($dryRun) {
            $this->getIO()->note('Dry run');
        } elseif (
            $input->isInteractive() &&
            !$this->getIO()->confirm(
                sprintf(
                    'There are %s documents to extract informations fichier_pes, do you want to continue ?',
                    $numberOfDocument
                ),
                false
            )
        ) {
            return 1;
        }

        $this->getIO()->progressStart($numberOfDocument);
        $this->getIO()->newLine();

        $errorNumber = 0;
        foreach ($arrayDocuments as $id_d) {
            $id_e = $this->documentEntite->getEntite($id_d)[0]['id_e'];
            $this->getIO()->writeln(
                sprintf("Extract fichier_pes for id_d=%s&id_e=%s", $id_d, $id_e)
            );
            try {
                if (!$dryRun) {
                    $this->extractPesInfo($id_d, $id_e);
                }
            } catch (Exception $e) {
                $this->getIO()->error($e->getMessage());
                $errorNumber++;
            }
            $this->getIO()->progressAdvance();
        }
        $this->getIO()->progressFinish();
        $this->getIO()->success(
            'Success for ' . ($numberOfDocument - $errorNumber) . ' and failure for ' . $errorNumber
        );
        return 0;
    }

    /**
     * @throws Exception
     */
    private function extractPesInfo(string $id_d, int $id_e): bool
    {
        $donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);
        $pes_etat_ack = $donneesFormulaire->get(self::FIELD_ETAT_ACK);
        $this->actionExecutorFactory->executeOnDocumentCritical($id_e, 0, $id_d, self::ACTION_EXTRACT);
        $this->donneesFormulaireFactory->get($id_d)->setData(self::FIELD_ETAT_ACK, $pes_etat_ack);

        return true;
    }
}
