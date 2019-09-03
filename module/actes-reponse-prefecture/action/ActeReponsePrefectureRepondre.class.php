<?php

class ActeReponsePrefectureRepondre extends ActionExecutor
{

    public function go()
    {
        $this->getDonneesFormulaire()->setData('repondre', true);
        return true;
    }

}