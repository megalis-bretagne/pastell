<?php

class LibersignTestButton extends ActionExecutor {

    public function go(){
        $this->redirect("connecteur/external-data.php?id_ce={$this->id_ce}&field=libersign_test");
    }

}
