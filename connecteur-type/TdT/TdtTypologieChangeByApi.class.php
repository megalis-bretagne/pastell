<?php

/**
 *
 * @deprecated PA 3.0.0
 * Il faut utiliser la fonction de l'API externalData et ne pas modifier directement type_acte et type_pj
 *
 *
 */
class TdtTypologieChangeByApi extends ConnecteurTypeActionExecutor
{
    /**
     * @return bool
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function go()
    {

        $result = [];

        $type_acte_element = $this->getMappingValue('type_acte');
        $type_pj_element = $this->getMappingValue('type_pj');
        $type_piece_element = $this->getMappingValue('type_piece');
        $type_piece_fichier_element = $this->getMappingValue('type_piece_fichier');

        $info = $this->displayAPI();

        $type_acte = $this->getDonneesFormulaire()->get($type_acte_element);
        $type_pj = json_decode($this->getDonneesFormulaire()->get($type_pj_element, "[]")) ?: [];

        if ($type_acte) {
            if (isset($info['actes_type_pj_list']) && ! array_key_exists($type_acte, $info['actes_type_pj_list'])) {
                throw new UnrecoverableException("Le type de pièce «" . $type_acte . "» ne correspond pas pour la nature et la classification selectionnée");
            }
            $result[] =  ['filename' => $info['pieces'][0], "typologie" => $info['actes_type_pj_list'][$type_acte] ?? $type_acte];
        }

        if ($type_pj) {
            if ((count($type_pj)) !== (count($info['pieces']) - 1)) {
                throw new UnrecoverableException("Le nombre de type de pièce «" . count($type_pj) . "» ne correspond pas au nombre d'annexe «" . (count($info['pieces']) - 1) . "»");
            }
            foreach ($type_pj as $i => $type) {
                if (isset($info['actes_type_pj_list']) && ! array_key_exists($type, $info['actes_type_pj_list'])) {
                    throw new UnrecoverableException("Le type de pièce «" . $type . "» ne correspond pas pour la nature et la classification selectionnée");
                }
                $result[] = ['filename' => $info['pieces'][$i + 1], "typologie" => $info['actes_type_pj_list'][$type] ?? $type];
            }
        }

        $this->getDonneesFormulaire()->setData(
            $type_piece_element,
            (count($type_pj) + 1) . " fichier(s) typé(s)"
        );

        $this->getDonneesFormulaire()->addFileFromData(
            $type_piece_fichier_element,
            'type_piece.json',
            json_encode($result)
        );

        return true;
    }

    /**
     * @throws Exception
     * @throws UnrecoverableException
     */
    public function displayAPI()
    {
        $result = [];

        $id_ce = $this->getConnecteurFactory()->getConnecteurId(
            $this->id_e,
            $this->type,
            TdtConnecteur::FAMILLE_CONNECTEUR
        );
        if (! $id_ce) {
            $result['pieces'] = $this->getAllPieces();
            return $result;
        }


        $classification_file_element = $this->getMappingValue('classification_file');
        $acte_nature = $this->getMappingValue('acte_nature');

        $actesTypePJData = new ActesTypePJData();

        $configTdt = $this->getConnecteurConfigByType(TdtConnecteur::FAMILLE_CONNECTEUR);
        $actesTypePJData->classification_file_path = $configTdt->getFilePath($classification_file_element);

        $actesTypePJData->acte_nature = $this->getDonneesFormulaire()->get($acte_nature);

        $actesTypePJ = $this->objectInstancier->getInstance(ActesTypePJ::class);

        $result['actes_type_pj_list'] = $actesTypePJ->getTypePJListe($actesTypePJData);
        if (! $result['actes_type_pj_list']) {
            throw new UnrecoverableException("Aucun type de pièce ne correspond pour la nature et la classification selectionnée");
        }

        $result['pieces'] = $this->getAllPieces();
        return $result;
    }

    /**
     * @return array|string
     * @throws UnrecoverableException
     */
    private function getAllPieces()
    {

        $arrete_element = $this->getMappingValue('arrete');
        $autre_document_attache = $this->getMappingValue('autre_document_attache');

        $pieces_list = $this->getDonneesFormulaire()->get($arrete_element);
        if (! $pieces_list) {
            throw new UnrecoverableException("La pièce principale n'est pas présente");
        }
        if ($this->getDonneesFormulaire()->get($autre_document_attache)) {
            $pieces_list = array_merge($pieces_list, $this->getDonneesFormulaire()->get($autre_document_attache));
        }
        return $pieces_list;
    }
}
