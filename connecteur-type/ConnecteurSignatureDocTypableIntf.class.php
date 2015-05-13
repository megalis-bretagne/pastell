<?php

/**
 * Interface d�crivant un connecteur de signature prenant en charge 
 * le typage (type, circuit) au niveau du document.
 */ 
interface ConnecteurSignatureDocTypableIntf {

    const CIRCUIT_ACTION_VISA = 'visa';
    const CIRCUIT_ACTION_SIGNATURE = 'signature';
    const CIRCUIT_ACTION_INCONNUE = 'inconnue';
    
    const KEY_CIRCUIT_ACTION = 'action';
    const KEY_CIRCUIT_ACTEUR = 'acteur';
    
    /**
     * Retourne la liste des types.
     * </p>
     * Les applications m�tiers doivent consid�rer cette information comme une 
     * option de configuration, dont la fr�quence de modification est faible. 
     * A ce titre, les types ne n�cessitent pas de mise en cache.
     * @return array(int => string)
     */
    public function getListTypes();

    /**
     * Retourne la liste des circuits d'un type
     * </p>
     * Les applications m�tiers doivent pouvoir acc�der � ces informations
     * m�me lorsque le parapheur cible n'est pas accessible. Il est donc 
     * pr�f�rable d'obtenir les circuits � partir d'un cache local (voir
     * @link ConnecteurSignatureDocTypableIntf::cacherListCircuits).
     * @return array(int => string)
     */
    public function getListCircuits($type);
    
    /**
     * Met en cache les circuits de la collectivit�.
     * </p>
     * Les applications m�tiers doivent pouvoir acc�der aux circuits m�me 
     * lorsque le parapheur cible n'est pas accessible. Il est donc 
     * pr�f�rable de mettre les circuits en cache.<br/>, 
     * En associant cette op�ration � une action du connecteur, elle peut alors 
     * �tre d�clench�e � fr�quence r�guli�re (par cron journalier par exemple),
     * ou ponctuellement, depuis la console par exemple.<br/>
     */
    public function cacherListCircuits();

    /**
     * Retourne le d�tail d'un circuit, constitu� de la liste de ses �tapes,
     * avec pour chacune, l'action et l'acteur.
     * @param type $type
     * @param type $circuit
     * @return array(
     *           int => array(
     *                    KEY_CIRCUIT_ACTEUR => string, 
     *                    KEY_CIRCUIT_ACTION => (string)CIRCUIT_ACTION_*
     *                  )
     *         )
     */
    public function getCircuitDetail($type, $circuit);
}
