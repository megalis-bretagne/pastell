<?php

class PieceMarcheAffectation extends ActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        if (!$this->getParametrage()) {
            return false;
        }
        $this->getJsonMetadata();
        $this->addActionOK("Les valeurs par défaut sont afféctées");

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function getParametrage()
    {
        $type_piece_marche = $this->getDonneesFormulaire()->get("type_piece_marche");
        if (! $type_piece_marche) {
            $this->setLastMessage("Le type de pièce est obligatoire");
            return false;
        }

        /** @var ParametrageFluxPieceMarche $parametrage_flux */
        $parametrage_flux = $this->getConnecteur('ParametragePieceMarche');

        $array_param = $parametrage_flux->getParametragePiece($type_piece_marche);

        if (! $array_param) {
            $this->setLastMessage("Il n'y a pas de valeurs par défaut retournées par le connecteur de paramétrage");
            return false;
        }
        foreach ($array_param as $key => $value) {
            $this->getDonneesFormulaire()->setData($key, $value);
        }

        return true;
    }

    public function getJsonMetadata()
    {
        //WTF ? Je vois pas à quoi ça sert : on affecte dans le document l'ensemble des méta-données à envoyer au parapheur ?

        /* Trop dangereux, je supprime pour le moment. Il faudra ajouter un autre champs si nécessaire, là on confond les méta-données parapheur avec autre chose*/
        /*$metadata = $this->getDonneesFormulaire()->getFileContent("json_metadata");
        $metadata = json_decode($metadata,true);
        if (!empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $this->getDonneesFormulaire()->setData($key,$value);
            }
        }

        return true;*/
    }
}
