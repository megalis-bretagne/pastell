<?php

class CPPUpdateStructure extends ActionExecutor
{
    public function go()
    {

        /** @var CPP $cpp */
        $cpp = $this->getMyConnecteur();
        $identifiant_structure = $this->getConnecteurProperties()->get('identifiant_structure');

        $identifiant_structure_cpp = $cpp->getIdentifiantStructureCPPByIdentifiantStructure($identifiant_structure);

        if (! $identifiant_structure_cpp) {
            if ($identifiant_structure) {
                $this->getConnecteurProperties()->setData('identifiant_structure_cpp', "1-IDENTIFIANT NON TROUVE");
                throw new Exception("L'identifiant de structure $identifiant_structure n'a pas été trouvé. L'identifiant CPP est invalide");
            } else {
                $this->getConnecteurProperties()->setData('identifiant_structure_cpp', "");
                throw new Exception("L'identifiant de structure CPP est vidé");
            }
        }

        $this->getConnecteurProperties()->setData('identifiant_structure_cpp', $identifiant_structure_cpp);
        $this->setLastMessage("Mise à jour de l'identifiant CPP à : $identifiant_structure_cpp");
        return true;
    }
}
