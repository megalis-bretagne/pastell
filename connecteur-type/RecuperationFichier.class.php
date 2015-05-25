<?php
abstract class RecuperationFichier extends Connecteur {
	
	/**
	 * Liste les fichiers disponible sur le connecteur ($directory)
	 * @return array
	 */
	abstract public function listFile();
	
	/**
	 * R�cup�re le fichier sur le connecteur et le sauvegarde sur le syst�me de fichier local
	 * @param string $filename nom du fichier � r�cuperer (retourn� dans la liste de listFile())
	 * @param string $destination_directory emplacement pour sauvegarder le fichier (sans le nom du fichier)
	 * @return boolean true si le fichier a �t� r�cup�r� et sauvegard�
	 * @throws Exception probl�me lors de la r�cuperation
	 */
	abstract public function retrieveFile($filename,$destination_directory);	
	
	/**
	 * D�truit le fichier sur le connnecteur
	 * @param string $filename nom du fichier � d�truire (retourn� dans la liste de listFile());
	 * @return boolean true si le fichier a �t� supprim�
	 * @throws Exception probl�me lors de la suppression
	 */
	abstract public function deleteFile($filename);
	
	/**
	 * D�pose fichier depuis le syst�me de fichiers local vers connecteur ($directory_send).
	 * @param string $source_directory emplacement o� se trouve le fichier (sans le nom du fichier) 
	 * @param string $filename nom du fichier � copier
	 * @return boolean true si le fichier a �t� d�pos�
	 * @throws Exception probl�me lors de la copie
	 */
	abstract public function sendFile($source_directory, $filename);
		
}
