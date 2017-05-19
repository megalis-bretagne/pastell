<?php

class LibersignTestV2 extends ChoiceActionExecutor  {

    public function go(){

    }

    public function displayAPI() {
        throw new Exception("Nothing to display");
    }

    public function display(){
        $this->libersignConnecteur = $this->getMyConnecteur();
        $this->renderPage("Test de Libersign",__DIR__."/../template/LibersignTest.php");
        return true;
    }
}