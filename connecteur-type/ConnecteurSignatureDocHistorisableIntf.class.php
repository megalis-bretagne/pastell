<?php

/**
 * Interface d�crivant un connecteur de signature prenant en charge 
 * l'historique de documents.
 */
interface ConnecteurSignatureDocHistorisableIntf {

    const KEY_LOG_TIMESTAMP = 'timestamp';
    const KEY_LOG_ACTEUR = 'acteur';
    const KEY_LOG_STATUS = 'status';
    const KEY_LOG_ANNOTATION = 'annotation';

    /**
     * Retourne l'historique d'un dossier.
     * Ce concept est impl�ment� de mani�re diff�rente d'un parapheur � l'autre. 
     * La structure renvoy�e est g�n�rique mais :
     * - la liste peut �tre vide
     * - certaines champs peuvent ne pas �tre renseign�es
     * - les valeurs ne sont pas conventionn�es et NE doivent PAS servir dans des expressions conditionnelles
     * @return array(
     *           int => array(
     *                    KEY_LOG_TIMESTAMP => string, format ISO (2014-05-06T14:27:06.789+02:00) 
     *                    KEY_LOG_STATUS => string,
     *                    KEY_LOG_ACTEUR => string,
     *                    KEY_LOG_ANNOTATION => string
     *                  )
     *         )
     */
    public function docHistorique();

    /**
     * Retourne le log historique correspondant au dernier visa ou signature.
     * @param array $histoAll historique du dossier (@see docHistorique)
     * @return null si aucun log correspondant
     */
    public function searchHistoriqueLogValidation($histoAll);
    
    /**
     * Retourne le log historique correspondant au dernier rejet de visa ou de signature.
     * @param array $histoAll historique du dossier (@see docHistorique)
     * @return null si aucun log correspondant
     */
    public function searchHistoriqueLogRejet($histoAll);
    
}
