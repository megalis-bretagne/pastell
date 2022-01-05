<?php

namespace Pastell\Command\Module;

use DocumentEntite;
use DocumentSQL;
use DonneesFormulaireFactory;
use Exception;
use InternalAPI;
use Pastell\Command\BaseCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ActesAddTypePieceFichier extends BaseCommand
{
    private const FIELD_TYPE_ACTE = 'type_acte';
    private const FIELD_TYPE_PJ = 'type_pj';
    private const FIELD_TYPE_PIECE = 'type_piece';
    private const FIELD_TYPE_PIECE_FICHIER = 'type_piece_fichier';
    private const NAME_TYPE_PIECE_FICHIER = 'type_piece.json';
    private const FIELD_ARRETE = 'arrete';
    private const FIELD_ANNEXE = 'autre_document_attache';

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
     * @var InternalAPI
     */
    private $internalAPI;

    public function __construct(
        DocumentSQL $documentSQL,
        DocumentEntite $documentEntite,
        DonneesFormulaireFactory $donneesFormulaireFactory,
        InternalAPI $internalAPI
    ) {
        $this->documentSQL = $documentSQL;
        $this->documentEntite = $documentEntite;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
        $this->internalAPI = $internalAPI;
        parent::__construct();
    }

    protected function configure()
    {
        // Fix Modification de l'affichage de la typologie en version [3.0.0] - 2019-10-14
        // Cf /connecteur-type/TdT/TdtChoiceTypologieActes.class.php commit 17/07/2019
        $this
            ->setName('app:module:actes-add-type-piece-fichier')
            ->setDescription('Build type_piece_fichier with type_acte and type_pj for source module actes')
            ->addArgument('source', InputOption::VALUE_REQUIRED, 'The source module')
        ;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = $input->getArgument('source');

        if (!$source) {
            throw new InvalidArgumentException("Missing source module actes");
        }
        $this->getIO()->title("Start build type_piece_fichier according to type_acte and type_pj for `$source`");

        $arrayDocuments = [];
        foreach ($this->documentSQL->getAllByType($source) as $document_info) {
            // ajout des actes V2 (envoi papier + typologie des pièces) en version [2.0.5] - 2018-04-30
            if (
                date("Y-m-d", strtotime($this->documentSQL->getInfo($document_info['id_d'])['creation']))
                >=  "2018-04-30"
            ) {
                $id_d = $document_info['id_d'];
                $donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);
                $typeActe = $donneesFormulaire->get(self::FIELD_TYPE_ACTE);
                if ($typeActe && !$donneesFormulaire->get(self::FIELD_TYPE_PIECE_FICHIER)) {
                    $arrayDocuments[$id_d] = array_merge(
                        [$typeActe],
                        json_decode($donneesFormulaire->get(self::FIELD_TYPE_PJ), true) ?? []
                    );
                }
            }
        }

        $numberOfDocument = count($arrayDocuments);
        if ($input->isInteractive()) {
            $question = "There are $numberOfDocument documents to build type_piece_fichier, do you want to continue ?";
            if (!$this->getIO()->confirm($question, false)) {
                return 0;
            }
        }

        $this->getIO()->progressStart($numberOfDocument);
        $this->internalAPI->setCallerType(InternalAPI::CALLER_TYPE_SCRIPT);
        $errorNumber = 0;
        foreach ($arrayDocuments as $id_d => $arrayTypePiece) {
            $id_e = $this->documentEntite->getEntite($id_d)[0]['id_e'];
            $apiPatch = "/entite/$id_e/document/$id_d/externalData/type_piece";
            $this->getIO()->newLine();
            $this->getIO()->writeln(
                'Do API Patch ' . $apiPatch . ' with data type_pj = ' . json_encode($arrayTypePiece)
            );
            try {
                $this->internalAPI->patch($apiPatch, ['type_pj' => $arrayTypePiece]);
            } catch (Exception $e) {
                $this->getIO()->writeln('Fail by API Patch: ' . $e->getMessage());
                $this->getIO()->writeln('Try with FieldTypePiece');
                try {
                    $this->tryWithFieldTypePiece($id_d);
                } catch (Exception $e) {
                    $this->getIO()->writeln('Fail with FieldTypePiece: ' . $e->getMessage());
                    $this->getIO()->writeln('Try with type_pj without control classification');
                    try {
                        $this->tryWithArrayTypePiece($id_d, $arrayTypePiece);
                    } catch (Exception $e) {
                        $this->getIO()->error($e->getMessage());
                        $errorNumber++;
                    }
                }
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
    private function tryWithFieldTypePiece($id_d): bool
    {
        $result = [];
        $donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);
        $arrayFieldTypePiece = explode(";", $donneesFormulaire->get(self::FIELD_TYPE_PIECE));
        if (! $arrayFieldTypePiece) {
            throw new Exception(
                'Impossible to explain field type_piece"' .  $donneesFormulaire->get(self::FIELD_TYPE_PIECE) . '"'
            );
        }
        foreach ($arrayFieldTypePiece as $pj) {
            $explodePJ = explode(":", $pj);
            if (count($explodePJ)  !== 2) {
                throw new Exception('Impossible to explain "' .  $pj . '"');
            }
            $result[] = ['filename' => $explodePJ[0], "typologie" => $explodePJ[1]];
        }
        $donneesFormulaire->addFileFromData(
            self::FIELD_TYPE_PIECE_FICHIER,
            self::NAME_TYPE_PIECE_FICHIER,
            json_encode($result)
        );
        return true;
    }

    /**
     * @throws Exception
     */
    private function tryWithArrayTypePiece($id_d, array $arrayTypePiece): bool
    {
        $result = [];
        $donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);

        if (empty($arrayTypePiece)) {
            throw new Exception("Aucun tableau type_pj fourni");
        }

        $piecesList = $donneesFormulaire->get(self::FIELD_ARRETE);
        if (! $piecesList) {
            throw new Exception("La pièce principale n'est pas présente");
        }
        if ($donneesFormulaire->get(self::FIELD_ANNEXE)) {
            $piecesList = array_merge($piecesList, $donneesFormulaire->get(self::FIELD_ANNEXE));
        }

        if ((count($arrayTypePiece)) !== (count($piecesList))) {
            throw new Exception(
                "Le nombre de type_pj fourni «" . count($arrayTypePiece) .
                "» ne correspond pas au nombre de documents (acte et annexes) «" . (count($piecesList)) . "»"
            );
        }
        foreach ($arrayTypePiece as $i => $type) {
            $result[] = ['filename' => $piecesList[$i], "typologie" => $type];
        }

        $donneesFormulaire->setData(self::FIELD_TYPE_PIECE, count($arrayTypePiece) . " fichier(s) typé(s)");
        $donneesFormulaire->addFileFromData(
            self::FIELD_TYPE_PIECE_FICHIER,
            self::NAME_TYPE_PIECE_FICHIER,
            json_encode($result)
        );
        return true;
    }
}
