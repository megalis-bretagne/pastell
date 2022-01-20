<?php

require_once __DIR__ . "/../lib/PieceMarcheParEtapeData.class.php";
require_once __DIR__ . "/../lib/PieceMarcheParEtapeCreationPiece.class.php";

class PieceMarcheParEtapeCreerPieces extends ActionExecutor
{
    public function go()
    {
        try {
            $result = $this->metier();
            $this->setLastMessage($result);
        } catch (Exception $e) {
            $this->setLastMessage('ERREUR : ' . $e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function metier()
    {
        /** @var TmpFolder $tmpFolder */
        $tmpFolder = $this->objectInstancier->getInstance(TmpFolder::class);
        $tmp_folder = $tmpFolder->create();

        try {
            $result = $this->goThrow($tmp_folder);
        } catch (Exception $e) {
            $tmpFolder->delete($tmp_folder);
            throw $e;
        }
        $tmpFolder->delete($tmp_folder);

        return $result;
    }

    /**
     * @param $tmp_folder
     * @return string
     * @throws Exception
     */
    private function goThrow($tmp_folder)
    {

        $donneesFormulaire = $this->getDonneesFormulaire();

        $pieces = $donneesFormulaire->get('piece');
        if (!$pieces) {
            throw new Exception("Les fichiers Pièces sont manquants.");
        }

        $types_pj = json_decode($donneesFormulaire->get('type_pj'));
        if (!$types_pj) {
            throw new Exception("La typologie des pièces est manquante.");
        }

        @ unlink($tmp_folder . "/empty");

        $pieceMarcheParEtapeData = new PieceMarcheParEtapeData();

        $pieceMarcheParEtapeData->id_e = $this->id_e;
        $pieceMarcheParEtapeData->id_u = $this->id_u;
        $pieceMarcheParEtapeData->envoyer = false;
        $pieceMarcheParEtapeData->libelle = $donneesFormulaire->get('libelle');
        $pieceMarcheParEtapeData->numero_marche = $donneesFormulaire->get('numero_marche');
        $pieceMarcheParEtapeData->type_marche = $donneesFormulaire->get('type_marche');
        $pieceMarcheParEtapeData->recurrent = $donneesFormulaire->get('recurrent');
        $pieceMarcheParEtapeData->numero_consultation = $donneesFormulaire->get('numero_consultation');
        $pieceMarcheParEtapeData->type_consultation = $donneesFormulaire->get('type_consultation');
        $pieceMarcheParEtapeData->etape = $donneesFormulaire->get('etape');
        $pieceMarcheParEtapeData->soumissionnaire = $donneesFormulaire->get('soumissionnaire');
        $pieceMarcheParEtapeData->date_document = $donneesFormulaire->get('date_document');
        $pieceMarcheParEtapeData->montant = $donneesFormulaire->get('montant');

        $result = array();
        foreach ($pieces as $num => $file_name) {
            $file_path = $donneesFormulaire->getFilePath('piece', $num);
            $result[] = $this->objectInstancier
                ->getInstance(PieceMarcheParEtapeCreationPiece::class)
                ->creerPieceMarche($pieceMarcheParEtapeData, array_shift($types_pj), $file_name, $file_path);
        }

        $message = count($result) . " dossier(s) Pièces de marché créé(s): " . '<br/>';
        foreach ($result as $line) {
            $message .= $line . '<br/>';
        }
        $this->addActionOK($message);
        $this->notify($this->action, $this->type, $message);

        return $message;
    }
}
