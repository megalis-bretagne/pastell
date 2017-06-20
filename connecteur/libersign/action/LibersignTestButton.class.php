<?php

class LibersignTestButton extends ActionExecutor {

    public function go(){
        $this->redirect("connecteur/externalData?id_ce={$this->id_ce}&field=libersign_test");
    }

}
