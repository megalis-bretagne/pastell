<?php

class DisplaySousType extends ActionExecutor
{
    /**
     * @throws Exception
     */
    public function go()
    {
        /** @var SignatureConnecteur $signature */
        $signature = $this->getMyConnecteur();

        $properties = $this->getConnecteurProperties();
        $all_sous_type = $signature->getSousType();
        if ($all_sous_type === false) {
            throw new Exception($signature->getLastError());
        }

        $message = sprintf(
            'Liste des sous-type pour le type %s : %s',
            $properties->get('iparapheur_type'),
            implode(', ', $all_sous_type)
        );
        $this->setLastMessage($message);
        return true;
    }
}
