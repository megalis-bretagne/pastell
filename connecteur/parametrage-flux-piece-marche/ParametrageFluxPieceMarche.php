<?php

class ParametrageFluxPieceMarche extends Connecteur
{
    /** @var  DonneesFormulaire */
    private $connecteurConfig;


    public function setConnecteurConfig(DonneesFormulaire $connecteurConfig)
    {
        $this->connecteurConfig = $connecteurConfig;
    }

    public function setPieceMarcheJsonByDefault(): bool
    {
        $filePath = $this->getDataDir() . '/connector/paramFluxPieceMarche/parametrage-piece-marche.json';

        if (!file_exists($filePath)) {
            return false;
        }
        $this->connecteurConfig->addFileFromCopy('piece_marche_json', 'parametrage-piece-marche.json', $filePath);
        return true;
    }

    public function isPieceMarcheJsonValide()
    {

        $json_content = $this->getPieceMarcheJson();
        $data = json_decode($json_content);
        return json_last_error() == JSON_ERROR_NONE;
    }

    public function getPieceMarcheJson()
    {

        return $this->connecteurConfig->getFileContent('piece_marche_json');
    }

    public function getParametragePiece($code_piece)
    {

        $piece_marche_json = $this->connecteurConfig->getFileContent('piece_marche_json');
        $piece_marche_array = json_decode($piece_marche_json, true);

        return $piece_marche_array[$code_piece];
    }
}
