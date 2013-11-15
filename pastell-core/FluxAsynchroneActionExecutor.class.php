<?php

require_once(PASTELL_PATH . "/pastell-core/FluxSynchroneActionExecutor.class.php");

/**
 * Enregistre la demande d'action de mani�re � pouvoir l'ex�cuter de mani�re 
 * asynchrone, c'est � dire plus tard.
 * 
 * Une tentative imm�diate est tout de m�me tent�e. 
 * 
 * En cas de succ�s, ceci permet : 
 * - d'�conomiser le d�lai avant d�clenchement des op�rations automatiques
 * - de lisser les acc�s aux services dans le temps, et �viter les pics 
 *   induits par les acc�s rapproch�s durant les op�rations automatiques.
 * 
 * La demande retourne toujours un OK fonctionnel. Ainsi, si la tentative d'action 
 * imm�diate a �chou�, l'erreur reste trac�e, mais elle n'apparait pas dans le 
 * retour de la demande.
 */
abstract class FluxAsynchroneActionExecutor extends FluxSynchroneActionExecutor {
    const ACTION_SYNCHRONE_DEFAUT = false;
    
    private $synchroneActionName;

    public function __construct(ObjectInstancier $objectInstancier, $synchroneActionName) {
        parent::__construct($objectInstancier);        
        $this->synchroneActionName = $synchroneActionName;
    }

    public function go() {
        // Dans tous les cas, la demande est enregistr�e.
        $goRet = parent::go();
        // Tentative imm�diate effectu�e
        try {
            if ($this->synchroneActionName === self::ACTION_SYNCHRONE_DEFAUT) {
                $this->synchroneActionName = substr($this->action, 0, strlen($this->action) - strlen('-demande'));
            }
            $this->objectInstancier->ActionExecutorFactory->executeOnDocumentThrow(
                    $this->id_d, 
                    $this->id_e, 
                    $this->id_u, 
                    $this->synchroneActionName, 
                    $this->id_destinataire,
                    $this->from_api,
                    $this->action_params);
        } catch (Exception $gofEx) {
            // Le r�sultat de l'action synchrone a �t� enregistr�. Selon l'�tat 
            // appliqu�, elle pourrait �tre retent�e par les op�rations automatiques.
            // On ne remonte pas l'erreur car le retour doit concerner la demande, 
            // qui a bien �t� enregistr�e.
        }
        return $goRet;
    }

    protected function goFonctionnel() {
        return array(
            self::GO_KEY_ETAT => self::GO_ETAT_OK,
            self::GO_KEY_MESSAGE => self::GO_MESSAGE_ACTION);
    }

}