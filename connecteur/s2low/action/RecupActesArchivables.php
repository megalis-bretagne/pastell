<?php

class RecupActesArchivables extends ActionExecutor
{
    public const FLUX_ACTES_SLOW = 'actes-slow';

    /**
     * @throws NotFoundException
     * @throws S2lowException
     * @throws Exception
     */
    public function go()
    {
        /** @var S2low $connecteur */
        $connecteur = $this->getMyConnecteur();
        $connecteur_info = $connecteur->getConnecteurInfo();
        $id_e = $connecteur_info['id_e'];

        /** @var DocumentTypeFactory $documentTypeFactory */
        $documentTypeFactory = $this->objectInstancier->getInstance(DocumentTypeFactory::class);
        $documentCreationService = $this->objectInstancier->getInstance(DocumentCreationService::class);

        if (!$documentTypeFactory->isTypePresent(self::FLUX_ACTES_SLOW)) {
            throw new RuntimeException(
                'Le type ' . self::FLUX_ACTES_SLOW . " n'existe pas sur cette plateforme Pastell"
            );
        }

        $file_list = $connecteur->listeActesArchivables();

        foreach ($file_list as $transaction_id => $files) {
            $simpleXMLWrapper = new SimpleXMLWrapper();
            $xml = $simpleXMLWrapper->loadString($files[0]->file_content);
            $xml->registerXPathNamespace('actes', S2low::ACTES_NAMESPACE);
            $attributes = $xml->attributes(S2low::ACTES_NAMESPACE);

            $new_id_d = $documentCreationService->createDocumentWithoutAuthorizationChecking(
                $id_e,
                self::FLUX_ACTES_SLOW
            );
            /** @var DonneesFormulaire $donneesFormulaire */
            $donneesFormulaire = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($new_id_d);
            $donneesFormulaire->setData('acte_nature', (string)$attributes['CodeNatureActe']);
            $donneesFormulaire->setData('numero_de_lacte', (string)$attributes['NumeroInterne']);
            $donneesFormulaire->setData('objet', (string)$xml->xpath('//actes:Objet')[0]);
            $donneesFormulaire->setData('date_de_lacte', (string)$attributes['Date']);
            $donneesFormulaire->setData('document_papier', (string)$xml->xpath('//actes:DocumentPapier')[0] === 'O');

            if (count($files) > 1) {
                $type_pj = [];
                foreach ($files as $index_fichier => $file) {
                    if ($index_fichier === 0) {
                        $donneesFormulaire->addFileFromData(
                            'aractes',
                            'aracte.xml',
                            $file->file_content
                        );
                    } elseif ($index_fichier === 1) {
                        $donneesFormulaire->setData('type_acte', $file->code_pj);
                        $donneesFormulaire->addFileFromData(
                            'arrete',
                            $file->posted_filename,
                            $file->file_content
                        );
                    } elseif ($index_fichier >= 2) {
                        $type_pj[] = $file->posted_filename . ' : ' . $file->code_pj . "\n";
                        $donneesFormulaire->addFileFromData(
                            'autre_document_attache',
                            $file->posted_filename,
                            $file->file_content,
                            $index_fichier - 2
                        );
                    }
                }

                $donneesFormulaire->setData('type_pj', $type_pj);
            }
            $classification = (string)$xml->xpath('//actes:CodeMatiere1')[0]->attributes('actes', true)['CodeMatiere'];
            $codeMatiere2 = $xml->xpath('//actes:CodeMatiere2');
            if (isset($codeMatiere2[0])) {
                $classification .= '.' . (string)$codeMatiere2[0]->attributes('actes', true)['CodeMatiere'];
            }
            $codeMatiere3 = $xml->xpath('//actes:CodeMatiere3');
            if (isset($codeMatiere3[0])) {
                $classification .= '.' . (string)$codeMatiere3[0]->attributes('actes', true)['CodeMatiere'];
            }
            $donneesFormulaire->setData('classification', $classification);
            $donneesFormulaire->addFileFromData(
                'bordereau',
                'bordereau.pdf',
                $connecteur->getBordereau($transaction_id)
            );

            $donneesFormulaire->addFileFromData(
                'acte_tamponne',
                'acte_tamponne.pdf',
                $connecteur->getActeTamponne($transaction_id)
            );
            foreach ($connecteur->getAnnexesTamponnees($transaction_id) as $i => $annexe) {
                $donneesFormulaire->addFileFromData('annexes_tamponnees', $annexe['filename'], $annexe['content'], $i);
            }
        }


        $this->setLastMessage("Les fichiers d'actes ont été récupérés");
        return true;
    }
}
