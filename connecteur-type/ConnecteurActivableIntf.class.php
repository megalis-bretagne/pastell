<?php
/**
 * Interface d�crivant un connecteur activable/d�sactivable.
 */ 
// TODO proposition de g�n�ralisation : supprimer cette interface et g�n�raliser dans Connecteur.class
//  - attribut de classe (ex : activate)
//  - attribut de formulaire, de nom g�n�rique (ex : activate)
//  - renommer les attributs de formulaires existants (ex : iparapheur_activate)
//  - dans les classes d�riv�es, utiliser la m�thode isActif au lieu de l'attribut
//  - activer les connecteurs ayant re�u ce nouvel attribut
interface ConnecteurActivableIntf {
    public function isActif();
}

