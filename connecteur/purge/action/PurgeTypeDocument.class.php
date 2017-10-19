<?php

class PurgeTypeDocument extends ChoiceActionExecutor {

    public function go(){
        $document_type = $this->getRecuperateur()->get('document_type');
        $list_flux = $this->displayAPI();
        if (empty($list_flux[$document_type])){
            throw new Exception("Ce type de document n'existe pas");
        }
        $this->getConnecteurProperties()->setData('document_type',$document_type);
        $this->getConnecteurProperties()->setData('document_type_libelle',$list_flux[$document_type]['nom']);
        return true;
    }

    public function displayAPI(){
        return $this->apiGet("/Flux",array());
    }

    public function display(){
        $this->document_type = $this->getConnecteurProperties()->get('document_type');
        $this->list_flux = $this->displayAPI();
        $this->renderPage(
            "Choix du type de document",
            __DIR__."/../template/PurgeTypeDocument.php"
            );
        return true;
    }
}