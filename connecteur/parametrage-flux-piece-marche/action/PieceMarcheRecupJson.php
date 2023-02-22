<?php

class PieceMarcheRecupJson extends ActionExecutor
{
    public function go()
    {
        /** @var ParametrageFluxPieceMarche $parametrageFluxPieceMarche */
        $parametrageFluxPieceMarche = $this->getMyConnecteur();

        $result = $parametrageFluxPieceMarche->setPieceMarcheJsonByDefault();
        if (!$result) {
            $this->setLastMessage("Le fichier n'a pas pu être retrouvé");
            return false;
        }
        $this->setLastMessage("Le fichier a été renseigné");
        return true;
    }
}
