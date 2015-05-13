<?php

require_once(PASTELL_PATH . "/pastell-core/ActionExecutor.class.php");

/**
 * Encadre les traitements fonctionnels d'une action en prenant en charge :
 * - la d�tection des erreurs d'acc�s aux services et le pilotage des 
 *   suspension/reprises du connecteur concern�
 * - la d�tection des services d�sactiv�s
 * - le log dans le journal, avec mention de l'appelant (application m�tier (api, cron) ou console)
 * - et d'informations compl�mentaires, � but statistiques essentiellement, et param�trables par action
 * - la distinction entre actions de workflow (pouvant modifier l'�tat) et 
 *   actions de recueil d'information (obtention sans modification)
 * - la redirection �ventuelle
 * Offre des m�thodes d�clenchables par les actions d�riv�es : 
 * - �mission de notification
 * Restent � la charge du fonctionnel :
 * - le traitement, acc�dant au(x) service(s)
 * - le calcul du r�sultat (prochain �tat, message, ...)
 * - red�finition du formattage d'affichage par d�faut, pour les actions d'obtention d'information
 */
abstract class FluxSynchroneActionExecutor extends ActionExecutor {

    // Attributs g�n�riques de flux 

    const FLUX_ATTR_OBJET = 'objet';
    const FLUX_ATTR_PILOTE = 'app_pilote';
    const FLUX_ATTR_ERREUR_DETAIL = 'erreur_detail';
    const FLUX_ATTR_DERNIERE_TENTATIVE_ACTION = 'derniere_tentative_action';
    // Valeurs conventionn�es pour FLUX_ATTR_*
    const FLUX_PILOTE_CONSOLE = 'console';
    // Cl�s pour retour goFonctionnel
    const GO_KEY_ETAT = 'goEtat';
    const GO_KEY_MESSAGE = 'goMessage';
    const GO_KEY_REDIRECT = 'goRedirect';
    const GO_KEY_JOURNALINFOS = 'goInfos';
    // Valeurs conventionn�es pour GO_KEY_*
    const GO_ETAT_INCHANGE = 'etat-inchange';
    const GO_ETAT_OK = 'etat-action';
    const GO_ETAT_AUCUN = 'etat-aucun';
    const GO_MESSAGE_ACTION = 'action-name';
    // Attributs pour le message du journal
    const KEY_JOURNAL_PILOTE = 'app';
    const KEY_JOURNAL_DOCTAILLE = 'taille'; // Taille du fichier document principal
    const KEY_JOURNAL_FICHIERSTAILLE = 'tailletot'; // Taille de tous les fichiers
    const KEY_JOURNAL_MESSAGE = 'msg';

    private $fluxActions;
    private $workflowActions;

    /**
     * Ex�cute le traitement fonctionnel de l'action.
     * <p>
     * Les erreurs sont remont�es sous forme d'exception.
     * <p>
     * Les exceptions �mises seront consid�r�es comme un �chec de l'action; s'il 
     * s'agit d'une action de workflow, le document passera alors � l'�tat <nom de l'action>-erreur.
     * <p>
     * Lorsque l'action est d�clench�e par la console, le r�sultat fonctionnel,
     * destin� � l'affichage, est soumis � conversion (@link FluxSynchroneActionExecutor::goFonctionnelDisplay).
     * <p>
     * @return array
     *          <ul>
     *          <li>GO_KEY_ETAT => prochain �tat du document. 
     *              Cet attribut est utilis� uniquement pour les actions agissant 
     *              sur le workflow (no-workflow = false).
     *                  <ul>
     *                  <li>GO_ETAT_INCHANGE :
     *                      action accomplie, mais pas de changement d'�tat; elle 
     *                      pourra �tre ex�cut�e � nouveau.
     *                      </li>
     *                  <li>GO_ETAT_OK :
     *                      action accomplie; le document passera dans l'�tat <nom de l'action>.
     *                      </li>
     *                  <li>string :
     *                      action accomplie; le document passera dans l'�tat mentionn�.
     *                      </li>
     *                  </ul>
     *              </li>
     *          <li>GO_KEY_MESSAGE => message du r�sultat.<br>
     *              Logu� lorsque l'action modifie le workflow.<br>
     *              Affich� lorsque l'action est appel�e depuis la console.<br>
     *              Si absent, vide ou GO_MESSAGE_ACTION, la valeur de l'attribut 
     *              Action::ACTION_DISPLAY_NAME de l'action sera utilis�.
     *              </li>
     *          <li>GO_KEY_REDIRECT => url de redirection<br>
     *              Redirection effectu�e en cas de traitement sans exception.<br>
     *              Si absent ou false, pas de redirection.
     *              </li>
     *         </ul>
     */
    abstract protected function goFonctionnel();

    /**
     * Retourne la pr�sentation HTML d'une valeur.
     * Par d�faut : <ul>
     * <li>un tableau est repr�sent� par une TABLE HTML. Le tableau doit �tre au format array(int TR => array(string TH => mixed TD)).</li>
     * <li>toute autre valeur est repr�sent�e par sa conversion en cha�ne</li>
     * </ul>
     */
    protected function goFonctionnelDisplay($message) {
        if (is_array($message)) {
            return $this->arrayDisplay($message);
        }
        return htmlentities($message);
    }

    public function go() {
        try {
            $gof = $this->goFonctionnel();
            if (!is_array($gof)) {
                throw new Exception("Format de retour 'goFonctionnel' incorrect");
            }
            $gofEtat = $gof[self::GO_KEY_ETAT];
            $gofMessage = @$gof[self::GO_KEY_MESSAGE];
            $gofJournalInfos = @$gof[self::GO_KEY_JOURNALINFOS];
            if ($gofEtat == self::GO_ETAT_INCHANGE) {
                $goRet = true;
                $goEtat = null;
                $goMessage = $gofMessage && ($gofMessage != self::GO_MESSAGE_ACTION) ? $gofMessage : 'Etat inchang� sur action \'' . $this->getActionDoName($this->action) . '\'';
            } elseif ($gofEtat == self::GO_ETAT_OK) {
                $goRet = true;
                $goEtat = $this->action;
                $goMessage = $gofMessage && ($gofMessage != self::GO_MESSAGE_ACTION) ? $gofMessage : $this->getActionName($goEtat);
            } else {
                $goRet = true;
                $goEtat = $gofEtat;
                $goMessage = $gofMessage && ($gofMessage != self::GO_MESSAGE_ACTION) ? $gofMessage : $this->getActionName($goEtat);
            }
            $actionAuto = $this->isActionAuto();
            // On ne journalise pas les actions automatiques qui ne changent pas l'�tat.
            // On optimise ainsi les volumes en �liminant les traces des actions automatiques "st�riles".
            if (!($actionAuto && ($gofEtat == self::GO_ETAT_INCHANGE))) {
                $this->logAction($goEtat, $goMessage, $gofJournalInfos);
            }
            // En contexte console, conversion pour affichage.
            if (!$actionAuto && !$this->from_api) {
                $goMessage = $this->goFonctionnelDisplay($goMessage);
            }
            $this->setLastMessage($goMessage);

            $gofRedirect = @$gof[self::GO_KEY_REDIRECT];
            if ($gofRedirect) {
                $this->redirect($gofRedirect);
            }
            if ($this->isWorkflow()) {
                // Mise � jour de la date du dernier essai de l'action
                $this->majDerniereTentativeAction($gofEtat == self::GO_ETAT_INCHANGE);
            }
            
            return $goRet;
        } catch (ConnecteurActivationException $gofEx) {
            // Les erreurs dues � la d�sactivation "volontaire" ne sont consid�r�es
            // - ni comme des erreurs d'acc�s : ne g�n�rent donc pas de suspension 
            // - ni comme des erreurs fonctionnelles : ne terminent donc pas le workflow
            // Erreur trac�e, �tat inchang�
            return $this->onException($gofEx, false);
        } catch (ConnecteurSuspensionException $gofEx) {
            // Erreur trac�e, �tat inchang�
            return $this->onException($gofEx, false);
        } catch (ConnecteurAccesException $gofEx) {
            // Gestion des suspensions
            try {
                $this->objectInstancier->ConnecteurSuspensionControler->onAccesEchec($gofEx->getConnecteur(), $gofEx);
            } catch (Exception $onAccesEchecEx) {
                // Erreur de gestion des suspensions => erreur trac�e, �tat d'erreur
                return $this->onException($onAccesEchecEx, true);
            }
            // Erreur trac�e, �tat inchang�
            return $this->onException($gofEx, false);
        } catch (Exception $gofEx) {
            // Erreur fonctionnelle => erreur trac�e, �tat d'erreur
            return $this->onException($gofEx, true);
        }
    }

    private function majDerniereTentativeAction($etat_inchange) {        
        $doc = $this->getDonneesFormulaire();
        if ($etat_inchange) {                
            $doc->setData(self::FLUX_ATTR_DERNIERE_TENTATIVE_ACTION, date('Y-m-d H:i:s'));
        } else {
            $doc->deleteField(self::FLUX_ATTR_DERNIERE_TENTATIVE_ACTION);
        }
    }
        
    private function onException(Exception $ex, /* boolean */ $changeEtatErreur) {
        $message = $ex->getMessage();
        // Journaliser
        if ($this->isWorkflow()) {
            $etat = $changeEtatErreur ? $this->action . '-erreur' : null;
            $this->logAction($etat, $message);
            // Mise � jour de la date du dernier essai de l'action
            $this->majDerniereTentativeAction(!$changeEtatErreur);
        }
        // Persister le d�tail de l'erreur dans le flux
        $messageDetail = exceptionToJson($ex);
        $doc = $this->getDonneesFormulaire();
        $doc->addFileFromData(self::FLUX_ATTR_ERREUR_DETAIL, 'erreur_detail', $messageDetail);
        // Signaler l'echec. Pas de throw, pour ne pas journaliser � nouveau.
        $this->setLastMessage($message);
        return false;
    }

    private function getFluxActions() {
        if (!$this->fluxActions) {
            $this->fluxActions = $this->getDocumentTypeFactory()->getFluxDocumentType($this->type)->getAction();
        }
        return $this->fluxActions;
    }

    private function getWorkflowActions() {
        if (!$this->workflowActions) {
            $this->workflowActions = $this->getDocumentActionEntite()->getAction($this->id_e, $this->id_d);
        }
        return $this->workflowActions;
    }

    private function isActionAuto() {
        return $this->id_u == 0;
    }

    private function isWorkflow() {
        $actions = $this->getFluxActions();
        $noworkflow = $actions->getProperties($this->action, Action::NO_WORKFLOW);
        return !$noworkflow;
    }

    public function getActionDoName($action) {
        $actions = $this->getFluxActions();
        return $actions->getDoActionName($action);
    }

    public function getActionName($action) {
        $actions = $this->getFluxActions();
        return $actions->getActionName($action);
    }

    private function logAction($action, $message, $infos = NULL) {
        $messageJournal = $this->getMessageJournal($message, $infos);
        if ($action && ($action != self::GO_ETAT_AUCUN)) {
            $this->getActionCreator()->addAction($this->id_e, $this->id_u, $action, $messageJournal);
        } else {
            $this->getJournal()->addSQL(Journal::DOCUMENT_ACTION, $this->id_e, $this->id_u, $this->id_d, $this->action, $messageJournal);
        }
    }

    private function getPilote() {
        // cr�ateur si api ou cron; console sinon.
        if ($this->from_api || $this->isActionAuto()) {
            $doc = $this->getDonneesFormulaire();
            $pilote = $doc->get(self::FLUX_ATTR_PILOTE) ?: "inconnu";
        } else {
            $pilote = self::FLUX_PILOTE_CONSOLE;
        }
        return $pilote;
    }

    protected function getMessageJournal($message, $infos = NULL) {
        // Note : le champ message dans la BD �tant limit� en taille, il y a risque de troncature de fin; 
        // on termine donc toujours par le message, les informations statistiques �tant prioritaires.
        $log = '';
        if (!isset($infos)) {
            $infos = array();
        }
        $pilote = @$infos[self::KEY_JOURNAL_PILOTE];
        if (!isset($pilote)) {
            $pilote = $this->getPilote();
            $infos = array(self::KEY_JOURNAL_PILOTE => $pilote) + $infos;
        }
        foreach ($infos as $key => $value) {
            $log .= $key . ':' . $value . ',';
        }
        $log .= self::KEY_JOURNAL_MESSAGE . ':' . $message;
        return $log;
    }

    private function arrayDisplay(array $array, array $columns = NULL) {
        if (count($array) == 0) {
            $display = htmlentities('aucun �l�ment');
        } else {
            $display = '<table border=1 cellspacing=0>';
            $display .= '<tr align="left">';
            if ($columns == NULL) {
                $columns = array_keys($array[0]);
            }
            foreach ($columns as $th) {
                $display .= '<th>' . htmlentities($th) . '</th>';
            }
            $display .= '</tr>';
            foreach ($array as $tr) {
                $display .= '<tr>';
                foreach ($columns as $th) {
                    $td = $tr[$th];
                    $datetime = strtotime($td);
                    if ($datetime !== false) {
                        $td = date("d/m/Y H:i:s", $datetime);
                    }
                    $display .= '<td>' . htmlentities($td) . '</td>';
                }
                $display .= '</tr>';
            }
            $display .= '</table>';
        }
        return $display;
    }

    protected function hasAction($action) {
        $workflowActions = $this->getWorkflowActions();
        foreach ($workflowActions as $workflowAction) {
            if ($workflowAction['action'] == $action) {
                return true;
            }
        }
        return false;
    }

    /**
     * Envoi un mail au destinataire
     * @param string $email email du destinataire. <br>
     *      Il n'est pas n�cessairement abonn� aux notifications.<br>
     * @param string $action action enregistr�e dans le journal des �v�nements
     * @param string $contenu contenu du message<br>
     *      Si mentionne un script (se termine par .php), le contenu du message
     *      sera le r�sultat de l'ex�cution de ce script
     * @param array $contenuScriptInfo informations transmises au script de contenu <br>
     *      Utilis� si le contenu se construit par script. C'est le script qui d�finit
     *      les informations dont il a besoin.
     *      Le tableau est associatif ! array('nomAttribut' => 'information', ...)
     *      Certaines informations y sont renseign�es par d�faut :
     *          'docObjet' => objet du document
     * @param string $sujet sujet du mail. Si null ou vide, un sujet par d�faut est utilis�.
     * @param type $emetteurName Nom de l'�metteur du mail. Si null ou vide, seul l'email de la plateforme �mettrice appara�tra.
     */
    protected function mail($email, $action, $contenu, array $contenuScriptInfo = array(), $sujet = null, $emetteurName = null) {
        $doc = $this->getDonneesFormulaire();
        $docObjet = $doc->get(self::FLUX_ATTR_OBJET);
        if (empty($sujet)) {
            $sujet = "Votre dossier " . $docObjet;
        }
        if (!isset($contenuScriptInfo['docObjet'])) {
            $contenuScriptInfo['docObjet'] = $docObjet;
        }
        $this->objectInstancier->MailTo->mail($email, $sujet, $contenu, $action, $contenuScriptInfo, $emetteurName, $this->id_e, $this->id_u, $this->id_d);
    }

}
