<?php

class UpdateSousType extends ActionExecutor
{
    public function go()
    {
        /** @var SignatureConnecteur $signature */
        $signature = $this->getMyConnecteur();

        $properties = $this->getConnecteurProperties();
        $all_sous_type = $signature->getSousType();
        if ($all_sous_type == false) {
            throw new Exception($signature->getLastError());
        }
        $content = "";
        foreach ($all_sous_type as $sous_type) {
            $content .= "$sous_type\n";
        }
        $properties->addFileFromData('iparapheur_sous_type', 'iparapheur_sous_type.txt', $content);
        $this->setLastMessage("Les sous-types ont été mis à jour");
        return true;
    }
}
