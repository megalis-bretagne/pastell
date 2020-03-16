<?php

namespace Pastell\Updater\Major3\Minor0;

use ConnecteurEntiteSQL;
use ConnecteurFactory;
use DocumentSQL;
use DonneesFormulaireException;
use DonneesFormulaireFactory;
use FastParapheur;
use NotFoundException;
use Pastell\Updater\Version;
use PastellLogger;
use TdTRecupActe;
use TypeDossierService;
use TypeDossierSQL;

class Patch2 implements Version
{
    /**
     * @var PastellLogger
     */
    private $pastellLogger;

    /**
     * @var ConnecteurEntiteSQL
     */
    private $connecteurEntiteSql;

    /**
     * @var ConnecteurFactory
     */
    private $connecteurFactory;

    /**
     * @var DocumentSQL
     */
    private $documentSql;

    /**
     * @var DonneesFormulaireFactory
     */
    private $donneesFormulaireFactory;

    /**
     * @var TypeDossierSQL
     */
    private $typeDossierSql;

    /**
     * @var TypeDossierService
     */
    private $typeDossierService;

    public function __construct(
        PastellLogger $pastellLogger,
        ConnecteurEntiteSQL $connecteurEntiteSQL,
        ConnecteurFactory $connecteurFactory,
        DocumentSQL $documentSQL,
        DonneesFormulaireFactory $donneesFormulaireFactory,
        TypeDossierSQL $typeDossierSQL,
        TypeDossierService $typeDossierService
    ) {
        $this->pastellLogger = $pastellLogger;
        $this->connecteurEntiteSql = $connecteurEntiteSQL;
        $this->connecteurFactory = $connecteurFactory;
        $this->documentSql = $documentSQL;
        $this->donneesFormulaireFactory = $donneesFormulaireFactory;
        $this->typeDossierSql = $typeDossierSQL;
        $this->typeDossierService = $typeDossierService;
    }

    /**
     * @throws NotFoundException
     */
    public function update(): void
    {
        $this->replaceFastParapheurUrl();
        $this->renameBordereauFieldToBordereauSignature();
    }

    private function replaceFastParapheurUrl(): void
    {
        // TODO: move legacy autoload into composer
        require_once PASTELL_PATH . '/connecteur/fast-parapheur/FastParapheur.class.php';

        $fastParapheurConnectors = $this->connecteurEntiteSql->getAllById('fast-parapheur');
        foreach ($fastParapheurConnectors as $fastParapheurConnector) {
            if ($fastParapheurConnector['id_e'] === '0') {
                continue;
            }
            $id_ce = $fastParapheurConnector['id_ce'];
            $connecteurConfig = $this->connecteurFactory->getConnecteurConfig($id_ce);
            $oldUrl = $connecteurConfig->get('wsdl');
            $newUrl = str_replace(FastParapheur::WSDL_URI, '', $oldUrl);

            $connecteurConfig->setData('wsdl', $newUrl);
            $this->pastellLogger->info('id_ce => ' . $id_ce);
            $this->pastellLogger->info('old URL => ' . $oldUrl);
            $this->pastellLogger->info('new URL => ' . $newUrl);
        }
    }

    /**
     * @throws NotFoundException
     * @throws DonneesFormulaireException
     */
    private function renameBordereauFieldToBordereauSignature(): void
    {
        // TODO: move legacy autoload into composer
        require_once PASTELL_PATH . '/connecteur-type/TdT/TdTRecupActe.class.php';

        $typeDossierWithSignatureStep = $this->getTypeDossierWithStep('signature');
        $bordereauFieldName = 'bordereau';
        $regex = "/^($bordereauFieldName)(_\d+)?$/";

        foreach ($typeDossierWithSignatureStep as $typeDossierId) {
            $documents = $this->documentSql->getAllByType($typeDossierId);
            foreach ($documents as $document) {
                $id_d = $document['id_d'];
                $donneesFormulaire = $this->donneesFormulaireFactory->get($id_d);
                $bordereauFields = preg_grep($regex, array_keys($donneesFormulaire->getFormulaire()->getFieldsList()));
                if (empty($bordereauFields)) {
                    continue;
                }
                foreach ($bordereauFields as $bordereauField) {
                    $matches = [];
                    preg_match($regex, $bordereauField, $matches);
                    $oldBordereauFieldName = $matches[0];
                    $bordereauFileName = $donneesFormulaire->getFileName($oldBordereauFieldName);
                    if (
                        !empty($bordereauFileName)
                        && !preg_match('/^(.*)' . TdTRecupActe::BORDEREAU_TDT_SUFFIX . '$/', $bordereauFileName)
                    ) {
                        $newBordereauFieldName = 'bordereau_signature' . ($matches[2] ?? '');
                        $donneesFormulaire->addFileFromCopy(
                            $newBordereauFieldName,
                            $bordereauFileName,
                            $donneesFormulaire->getFilePath($oldBordereauFieldName)
                        );
                        $donneesFormulaire->removeFile($oldBordereauFieldName);
                        $this->pastellLogger->info(
                            "Champ `$oldBordereauFieldName` => `$newBordereauFieldName` sur le document : $id_d "
                        );
                    }
                }
            }
        }
    }

    /**
     * @param string $step
     * @return array
     */
    private function getTypeDossierWithStep(string $step): array
    {
        $typeDossier = $this->typeDossierSql->getAll();
        $typeDossierWithSignatureStep = [];
        foreach ($typeDossier as $type_dossier_info) {
            $typeDossierData = $this->typeDossierService->getTypeDossierProperties($type_dossier_info['id_t']);
            if ($this->typeDossierService->hasStep($typeDossierData, $step)) {
                $typeDossierWithSignatureStep[] = $typeDossierData->id_type_dossier;
            }
        }
        return $typeDossierWithSignatureStep;
    }
}
