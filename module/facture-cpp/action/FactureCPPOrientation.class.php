<?php

class FactureCPPOrientation extends ActionExecutor
{
    private function notifyAndExit($message)
    {
        $this->notify($this->action, $this->type, $message);
        throw new Exception($message);
    }

    public function go()
    {

        $documentActionEntite = $this->getDocumentActionEntite();
        $last_action = $documentActionEntite->getLastAction($this->id_e, $this->id_d);
        $donneesFormulaire = $this->getDonneesFormulaire();
        $envoi_visa = $donneesFormulaire->get('envoi_visa');
        $envoi_ged = $donneesFormulaire->get('envoi_ged');
        $check_mise_a_dispo_gf = $donneesFormulaire->get('check_mise_a_dispo_gf');
        $envoi_sae = $donneesFormulaire->get('envoi_sae');
        $envoi_auto = $donneesFormulaire->get('envoi_auto');
        $action_cible = 'modification';

        if ($envoi_auto) {
            switch ($last_action) {
                case 'importation':
                case 'integration-glaneur-pivot':
                    if (! $donneesFormulaire->isValidable()) {
                        $this->notifyAndExit("Impossible de faire avancer le document depuis l'état : $last_action");
                    }
                    if ($envoi_visa == true) {
                        $action_cible = 'prepare-iparapheur';
                    } elseif ($envoi_ged == true) {
                        $action_cible = 'prepare-ged';
                    } elseif ($check_mise_a_dispo_gf == true) {
                        $action_cible = 'prepare-mise-a-dispo-gf';
                    } elseif ($envoi_sae == true) {
                        $action_cible = 'preparation-send-sae';
                    } else {
                        $action_cible = 'termine';
                    }
                    break;
                case 'send-iparapheur-annule':
                    if ($envoi_ged == true) {
                        $action_cible = 'prepare-ged';
                    } elseif ($check_mise_a_dispo_gf == true) {
                        $action_cible = 'prepare-mise-a-dispo-gf';
                    } elseif ($envoi_sae == true) {
                        $action_cible = 'preparation-send-sae';
                    } else {
                        $action_cible = 'termine';
                    }
                    break;
                case 'cpp-modif-statut-ok':
                    if ($envoi_ged == true) {
                        $action_cible = 'prepare-ged';
                    } elseif ($check_mise_a_dispo_gf == true) {
                        $action_cible = 'prepare-mise-a-dispo-gf';
                    } elseif ($envoi_sae == true) {
                        $action_cible = 'preparation-send-sae';
                    } else {
                        $action_cible = 'termine';
                    }
                    break;
                case 'send-ged':
                    if ($check_mise_a_dispo_gf == true) {
                        $action_cible = 'prepare-mise-a-dispo-gf';
                    } elseif ($envoi_sae == true) {
                        $action_cible = 'preparation-send-sae';
                    } else {
                        $action_cible = 'termine';
                    }
                    break;
                case 'mise-a-dispo-gf':
                    if ($envoi_sae == true) {
                        $action_cible = 'preparation-send-sae';
                    } else {
                        $action_cible = 'termine';
                    }
                    break;

                default:
                    $this->notifyAndExit("Impossible de faire avancer le document depuis l'état : $last_action");
            }
        }

        $this->getActionCreator()->addAction($this->id_e, 0, $action_cible, "Affectation automatique du nouvel état");
        $this->setLastMessage("Préparation pour le prochain état : $action_cible");
        return true;
    }
}
