<?php

class ActeReponsePrefectureRepondre extends ActionExecutor
{
    public function go()
    {
        $this->getDonneesFormulaire()->setData('repondre', true);
        $this->redirect(sprintf("Document/edition?id_d=%s&id_e=%s&page=1", $this->id_d, $this->id_e));

        return true;
    }
}
