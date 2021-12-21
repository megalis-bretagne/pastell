<?php

class LibersignTestButton extends ActionExecutor
{
    public function go()
    {
        $this->redirect("Connecteur/externalData?id_ce={$this->id_ce}&field=libersign_test");
    }
}
