<?php

/**
 * Interface d�crivant un connecteur prenant en charge 
 * la suppression de documents.
 */ 
require_once(PASTELL_PATH . "/pastell-core/DonneesFormulaire.class.php");

interface ConnecteurDocSupprimableIntf {

    const SUPPR_KEY_ETAT = 'etat';
    const SUPPR_KEY_MESSAGE = 'message';
    const SUPPR_ETAT_OK = 'ok';
    const SUPPR_ETAT_REPORT = 'report';

    /**
     * Supprime un dossier.
     * @see Connecteur::getDocDonneesFormulaire()
     * @return mixed
     *         <ul>
     *         <li>array
     *              <ul>
     *              <li>SUPPR_KEY_ETAT => r�sultat d'une suppression sans �chec<br>
     *                  SUPPR_ETAT_OK : la suppression a r�ussi<br>
     *                  SUPPR_ETAT_REPORT : le dossier sur le service n'est pas 
     *                      dans un �tat permettant la suppression imm�diate; mais
     *                      son �tat pourra changer et la suppression �tre tent�e 
     *                      � nouveau.<BR/> 
     *                      L'�tat du dossier bus reste donc inchang�, aucune erreur
     *                      n'est signal�e.
     *                  </li>
     *              <li>SUPPR_KEY_MESSAGE => message du r�sultat d'une suppression sans �chec<br>
     *                  </li>
     *              </ul>
     *              </li>
     *         <li>autre
     *              �quivalent SUPPR_ETAT_OK, sans message
     *              </li>
     *         </ul>
     * 
     * @throws Exception Echec de la suppression sur le service.
     */
    public function docSupprimer();
}
