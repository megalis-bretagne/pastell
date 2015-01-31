<?php
abstract class RecuperationFichier extends Connecteur {
	
	/**
	 * Liste les fichiers disponible sur le connecteur
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
	 */
	abstract public function deleteFile($filename);
		
}