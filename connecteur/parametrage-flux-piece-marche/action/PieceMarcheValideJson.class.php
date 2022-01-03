<?php

class PieceMarcheValideJson extends ActionExecutor
{
    public function go()
    {
        /** @var ParametrageFluxPieceMarche $parametrageFluxPieceMarche */
        $parametrageFluxPieceMarche = $this->getMyConnecteur();

        if (! $parametrageFluxPieceMarche->getPieceMarcheJson()) {
            $this->setLastMessage("Le fichier json est manquant.");
            return false;
        }

        $result = $parametrageFluxPieceMarche->isPieceMarcheJsonValide();
        if (! $result) {
            $this->setLastMessage("Le fichier présente une erreur de format json.");
            return false;
        }
        $this->setLastMessage("Le fichier est au format json.");
        return true;
    }
}
