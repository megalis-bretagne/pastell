<?php

class PieceMarcheParEtapeTypePiece extends ChoiceActionExecutor
{
    /**
     * @throws Exception
     */
    public function display()
    {

        $document_info = $this->getDocument()->getInfo($this->id_d);
        $this->setViewParameter('info', $document_info);

        $result = $this->displayAPI();
        $this->setViewParameter('pieces_type_pj_list', $result['pieces_type_pj_list']);
        $this->setViewParameter('pieces', $result['pieces']);

        $type_pj_selection = [];
        $type_pj = $this->getDonneesFormulaire()->get('type_pj');
        if ($type_pj) {
            $type_pj_selection = array_merge($type_pj_selection, json_decode($type_pj));
        }
        $type_pj_selection = array_pad($type_pj_selection, count($this->getViewParameter()['pieces']), 0);

        $this->setViewParameter('type_pj_selection', $type_pj_selection);

        $this->renderPage('Choix des types de pièces', 'module/pieceMarcheParEtape/PieceMarcheLotTypePiece');
    }

    /**
     * @throws Exception
     */
    public function displayAPI()
    {
        $result = [];

        $result['pieces_type_pj_list'] = $this->getTypePJListe();

        $result['pieces'] = $this->getDonneesFormulaire()->get('piece');
        if (! $result['pieces']) {
            throw new Exception("Les pièces ne sont pas présentes");
        }

        return $result;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {

        $result = [];
        $info = $this->displayAPI();

        $type_pj = $this->getRecuperateur()->get('type_pj');
        if (! $type_pj) {
            throw new Exception("Aucun type_pj fourni");
        }

        foreach ($type_pj as $i => $type) {
            $result[] = $info['pieces'][$i] . " : " . $info['pieces_type_pj_list'][$type];
        }

        $this->getDonneesFormulaire()->setData('type_piece', implode(" ; \n", $result));

        $this->getDonneesFormulaire()->setData('type_pj', json_encode($type_pj));

        return true;
    }

    public function getTypePJListe()
    {

        $type_piece_marche =  [
            "ARN" => "Accusé de Réception de Notification (ARN)",
            "AE" => "Acte d'Engagement (AE)",
            "AS" => "Acte de sous-traitance (AS)",
            "AN" => "Annexes (AN)",
            "AL" => "Annonces Légales (AL)",
            "AU" => "Autres (AU)",
            "APD" => "Avant Projet Détaillé (APD)",
            "APS" => "Avant Projet Sommaire (APS)",
            "AV" => "Avenant (AV)",
            "AC" => "Avis d'appel à la Concurrence (AC)",
            "BC" => "Bon de commande (BC)",
            "BPU" => "Bordereau des Prix Unitaires (BPU)",
            "CCAP" => "Cahier des Clauses Administratives Particulières (CCAP)",
            "CCTP" => "Cahier des Clauses Techniques Particulières (CCTP)",
            "CA" => "Courrier d'Attribution (CA)",
            "CM" => "Courrier marché générique (CM)",
            "CN" => "Courrier de notification (CN)",
            "DS" => "Déclaration sans suite (DS)",
            "DPGF" => "Décomposition du Prix Global et Forfaitaire (DPGF)",
            "DG" => "Décompte général et définitif (DG)",
            "DQE" => "Détail Quantitatif Estimatif (DQE)",
            "DR" => "Dossier de Réponse (DR)",
            "EC" => "Échange en cours de Consultation (EC)",
            "EA" => "Etat d'acompte (EA)",
            "E" => "Étude (E)",
            "LR" => "Lettre de Rejet (LR)",
            "LC" => "Liste des Candidatures (LC)",
            "MP" => "Mise au point (MP)",
            "OS" => "Ordre de service (OS)",
            "P" => "Programme (P)",
            "AO" => "Rapport d'Analyse des Offres (A0)",
            "CAO" => "Rapport de Commission d'Appel d'Offres (CAO)",
            "COP" => "Rapport de Commission d'Ouverture des Plis (COP)",
            "RP" => "Rapport de Présentation (RP)",
            "RDP" => "Récépissé de dépôt de pli (RDP)",
            "RC" => "Règlement de la Consultation (RC)",
        ];
        return $type_piece_marche;
    }
}
