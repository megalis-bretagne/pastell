<?php

class HorodateurInterneTestJournal extends ActionExecutor
{
    public function go()
    {
        $message = 'Ceci est une ligne de test ' . mt_rand(0, mt_getrandmax());
        $id_j = $this->getJournal()->add(Journal::TEST, 0, '', 'test', $message);
        $info = $this->getJournal()->getAllInfo($id_j);

        /** @var Horodateur $horodateur */
        $horodateur = $this->getMyConnecteur();

        $horodateur->verify($info['message_horodate'], $info['preuve']);
        $this->setLastMessage("Enregistrement de la ligne #$id_j<br/>" . "Vérification: OK");
        return true;
    }
}
