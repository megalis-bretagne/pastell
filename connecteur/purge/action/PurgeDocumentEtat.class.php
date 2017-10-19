<?php

class PurgeDocumentEtat extends ChoiceActionExecutor {

    public function go(){
        $document_etat = $this->getRecuperateur()->get('document_etat');
        $list_etat = $this->displayAPI();
        if (empty($list_etat[$document_etat])){
            throw new Exception("Cet état n'existe pas");
        }
        $this->getConnecteurProperties()->setData('document_etat',$document_etat);
        $this->getConnecteurProperties()->setData('document_etat_libelle',$list_etat[$document_etat]['name']);
        return true;
    }

    public function displayAPI(){
        $document_type = $this->getConnecteurProperties()->get('document_type');
        if (! $document_type){
            throw new Exception("Il faut d'abord choisir un type de document");
        }
        return $this->apiGet("/Flux/$document_type/action",array());
    }

    public function display(){
        $this->document_etat = $this->getConnecteurProperties()->get('document_etat');
        $this->list_etat = $this->displayAPI();
        $this->renderPage(
            "Choix de l'état du document",
            __DIR__."/../template/PurgeDocumentEtat.php"
        );
        return true;
    }

}