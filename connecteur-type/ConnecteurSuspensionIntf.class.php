<?php

/**
 * Interface d�crivant un connecteur prenant en charge la suspension automatique
 * d'acc�s au service.
 */
// TODO proposition de g�n�ralisation : supprimer cette interface et g�n�raliser dans Connecteur.class.
// Les m�thodes ne seraient pas abstraites et fourniraient un comportement par d�faut
// compatible avec l'ancien fonctionnement : pas de limite de tentatives.
interface ConnecteurSuspensionIntf {

    /**
     * Retourne les donn�es du connecteur
     * @return DonneesFormulaire
     */
    public function getConnecteurConfig();

    /**
     * Ev�nement d�clench� lorsqu'une tentative d'acc�s a �chou�.
     * @param array $tentativesContext contexte de calcul des tentatives
     *      En entr�e
     *          Le contexte reprend le calcul effectu� lors du pr�c�dent �chec.
     *          Un contexte ind�fini (false) signale qu'aucun cas d'�chec n'a 
     *          pr�c�d�, ou que le contexte �t� r�initialis� (reprise apr�s suspension).
     *      En sortie
     *          Le contexte peut �tre modifi�; il sera persist� et fourni lors 
     *          du prochain appel.
     * @return true : les tentatives peuvent se poursuivre
     *         false : la limite est atteinte; les acc�s seront suspendus
     */
    public function onAccesEchec(&$tentativesContext);
    
    /**
     * Retourne les informations du connecteur entit�.
     * @return array
     */
    public function getConnecteurInfo();
    
    /**
     * Retourne le nom du serveur cible (le FQDN en g�n�ral).
     * @return FALSE si le nom n'est pas d�fini
     */
    public function getServerName();
    
}

