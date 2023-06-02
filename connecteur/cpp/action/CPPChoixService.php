<?php

class CPPChoixService extends ChoiceActionExecutor
{
    /**
     * @return bool
     * @throws Exception
     */
    public function go()
    {
        $recuperateur = $this->getRecuperateur();
        $idService = $recuperateur->get('idService');
        if (! $idService) {
            $this->getConnecteurProperties()->setData('service_destinataire_libelle', "");
            $this->getConnecteurProperties()->setData('service_destinataire', '');
            return true;
        }

        $service_list = $this->displayAPI();

        foreach ($service_list['listeServices'] as $service_info) {
            if ($service_info['idService'] == $idService) {
                $this->getConnecteurProperties()->setData(
                    'service_destinataire_libelle',
                    "{$service_info['libelleService']} ({$service_info['codeService']})"
                );
                $this->getConnecteurProperties()->setData('service_destinataire', $service_info['idService']);
            }
        }
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function display()
    {
        $this->setViewParameter('service_list', $this->displayAPI());
        $this->renderPage("Choix d'un service Chorus Pro", 'connector/cpp/CPPChoixServiceTemplate');
        return true;
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function displayAPI()
    {
        /** @var CPP $cpp */
        $cpp = $this->getMyConnecteur();
        return $cpp->getListeService();
    }
}
