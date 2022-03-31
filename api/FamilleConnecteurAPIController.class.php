<?php

class FamilleConnecteurAPIController extends BaseAPIController
{
    private $connecteurDefinitionFiles;
    private $connecteurEntiteSQL;

    public function __construct(ConnecteurDefinitionFiles $connecteurDefinitionFiles, ConnecteurEntiteSQL $connecteurEntiteSQL)
    {
        $this->connecteurDefinitionFiles = $connecteurDefinitionFiles;
        $this->connecteurEntiteSQL = $connecteurEntiteSQL;
    }

    public function get()
    {
        $this->checkDroit(0, "system:lecture");

        $famille_connecteur = $this->getFromQueryArgs(0);
        if ($famille_connecteur) {
            return $this->detail($famille_connecteur);
        }

        $global = $this->getFromRequest('global');
        if ($global) {
            return $this->connecteurDefinitionFiles->getAllGlobalType();
        }
        return $this->connecteurDefinitionFiles->getAllType();
    }

    private function detail($famille_connecteur)
    {
        $id_connecteur = $this->getFromQueryArgs(1);

        if ($id_connecteur) {
            return $this->detailConnecteur($id_connecteur);
        }

        $global = $this->getFromRequest('global');
        return $this->connecteurDefinitionFiles->getAllByFamille($famille_connecteur, $global);
    }

    private function detailConnecteur($id_connecteur)
    {
        $global = $this->getFromRequest('global');
        return $this->connecteurDefinitionFiles->getInfo($id_connecteur, $global) ?: [];
    }
}
