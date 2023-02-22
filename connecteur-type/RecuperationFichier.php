<?php

abstract class RecuperationFichier extends Connecteur
{
    /**
     * Liste les fichiers disponible sur le connecteur ($directory)
     * @return array
     */
    abstract public function listFile();

    /**
     * Récupère le fichier sur le connecteur et le sauvegarde sur le système de fichier local
     * @param string $filename nom du fichier à récupérer (retourné dans la liste de listFile())
     * @param string $destination_directory emplacement pour sauvegarder le fichier (sans le nom du fichier)
     * @return boolean true si le fichier a été récupéré et sauvegardé
     * @throws Exception problème lors de la récupération
     */
    abstract public function retrieveFile($filename, $destination_directory);

    /**
     * Détruit le fichier sur le connnecteur
     * @param string $filename nom du fichier à détruire (retourné dans la liste de listFile());
     * @return boolean true si le fichier a été supprimé
     * @throws Exception problème lors de la suppression
     */
    abstract public function deleteFile($filename);

    /**
     * Dépose fichier depuis le système de fichiers local vers connecteur ($directory_send).
     * @param string $source_directory emplacement où se trouve le fichier (sans le nom du fichier)
     * @param string $filename nom du fichier à copier
     * @return boolean true si le fichier a été déposé
     * @throws Exception problème lors de la copie
     */
    abstract public function sendFile($source_directory, $filename);
}
