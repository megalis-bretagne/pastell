<?php

class ActesTamponner extends ActionExecutor
{
    public function go()
    {
        $tdtRetriever = $this->objectInstancier->getInstance(TdtRetriever::class);
        $result = $tdtRetriever->stampAgain($this->type, $this->id_e, $this->id_d);
        if (!$result) {
            $this->setLastMessage($tdtRetriever->getLastMessage());
            return false;
        }
        $this->setLastMessage("L'acte et les annexes ont été re-tamponné");
        return true;
    }

    public function goLot(array $all_id_d)
    {
        foreach ($all_id_d as $id_d) {
            $donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);
            if (! $donneesFormulaire->get('acte_use_publication_date')) {
                $donneesFormulaire->setData('acte_use_publication_date', true);
            }
            if (! $donneesFormulaire->get('acte_publication_date')) {
                $donneesFormulaire->setData('acte_publication_date', date("Y-m-d"));
            }
        }
        parent::goLot($all_id_d);
    }
}
