<?php

class PastellUpdater
{

    /**
     * @var PastellLogger
     */
    private $pastellLogger;

    /**
     * @var ObjectInstancier
     */
    private $objectInstancier;

    public function __construct(PastellLogger $pastellLogger, ObjectInstancier $objectInstancier)
    {
        $this->pastellLogger = $pastellLogger;
        $this->objectInstancier = $objectInstancier;
    }

    /**
     * @throws NotFoundException
     */
    public function update()
    {
        $this->to301();
        $this->to302();
    }

    public function to301()
    {
        $this->pastellLogger->info('Start script to 3.0.1');
        if (!file_exists(HTML_PURIFIER_CACHE_PATH)) {
            mkdir(HTML_PURIFIER_CACHE_PATH, 0755, true);
        }
        chown(HTML_PURIFIER_CACHE_PATH, DAEMON_USER);
        $this->pastellLogger->info('End script to 3.0.1');
    }

    /**
     * @throws NotFoundException
     */
    public function to302()
    {
        $this->pastellLogger->info('Start script to 3.0.2');

        $this->replaceFastParapheurUrl();
        $this->renameBordereauFieldToBordereauSignature();

        $this->pastellLogger->info('End script to 3.0.2');
    }

    private function replaceFastParapheurUrl(): void
    {
        $connecteurEntiteSql = $this->objectInstancier->getInstance(ConnecteurEntiteSQL::class);
        $fastParapheurConnectors = $connecteurEntiteSql->getAllById('fast-parapheur');
        foreach ($fastParapheurConnectors as $fastParapheurConnector) {
            if ($fastParapheurConnector['id_e'] === '0') {
                continue;
            }
            $id_ce = $fastParapheurConnector['id_ce'];
            $this->objectInstancier->getInstance(ConnecteurFactory::class)->getConnecteurById($id_ce);
            $connecteurConfig = $this->objectInstancier->getInstance(ConnecteurFactory::class)
                ->getConnecteurConfig($id_ce);
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
     */
    private function renameBordereauFieldToBordereauSignature(): void
    {
        $typeDossierWithSignatureStep = $this->getTypeDossierWithStep('signature');

        $bordereauFieldName = 'bordereau';
        $regex = "/^($bordereauFieldName)(_\d+)?$/";

        foreach ($typeDossierWithSignatureStep as $typeDossierId) {
            $documents = $this->objectInstancier->getInstance(DocumentSQL::class)->getAllByType($typeDossierId);
            foreach ($documents as $document) {
                $id_d = $document['id_d'];
                $donneesFormulaire = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($id_d);

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
                        $newBordereauFieldName = 'bordereau_signature' . $matches[2] ?? '';
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
        $typeDossier = $this->objectInstancier->getInstance(TypeDossierSQL::class)->getAll();
        $typeDossierService = $this->objectInstancier->getInstance(TypeDossierService::class);
        $typeDossierWithSignatureStep = [];
        foreach ($typeDossier as $type_dossier_info) {
            $typeDossierData = $typeDossierService->getTypeDossierProperties($type_dossier_info['id_t']);
            if ($typeDossierService->hasStep($typeDossierData, $step)) {
                $typeDossierWithSignatureStep[] = $typeDossierData->id_type_dossier;
            }
        }
        return $typeDossierWithSignatureStep;
    }
}
