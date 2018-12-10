<?php



abstract class SEDAConnecteur extends  Connecteur {

	/**
	 * @deprecated
	 * @param array $transactionsInfo
	 * @return string
	 */
	abstract public function getBordereau(array $transactionsInfo);

	/**
	 * Crée le bordereau en fonction des informations provenant du flux
	 * @param FluxData $fluxData
	 * @return string
	 */
	abstract public function getBordereauNG(FluxData $fluxData);

	/**
	 * Permet de valider un bordereau SEDA en fonction des schéma du connecteur
	 * @param string $bordereau
	 * @return bool
	 */
	abstract public function validateBordereau(string $bordereau);

	/**
	 * Permet de récupérer les erreurs provenant de la validation du bordereau SEDA
	 * @return LibXMLError[]
	 */
	abstract public function getLastValidationError();

	/**
	 *
	 * Génère l'archive en fonction des données du flux sur archive_path
	 * @param FluxData $fluxData
	 * @param string $archive_path
	 * @return false
	 */
	abstract public function generateArchive(FluxData $fluxData,string $archive_path);

	/**
	 * @param $file_path
	 * @return array
	 * @throws Exception
	 */
	protected function getInfoARActes($file_path){
		$file_name = basename($file_path);
		@ $xml = simplexml_load_file($file_path);
		if ($xml === false){
			throw new Exception("Le fichier AR actes $file_name n'est pas exploitable");
		}
		$namespaces = $xml->getNameSpaces(true);
		if (empty($namespaces['actes'])){
			throw new Exception("Le fichier AR actes $file_name n'est pas exploitable");
		}
		
		$attr = $xml->attributes($namespaces['actes']);
		if (!$attr){
			throw new Exception("Le fichier AR actes $file_name n'est pas exploitable");
		}
		return array('DateReception' => $attr['DateReception'],'IDActe'=>$attr['IDActe']);
	}
	
	public function getIntegrityMarkup($fileName){
		$node = new ZenXML("Integrity");
		$node->{'Contains'} = hash_file("sha256",$fileName);
		$node->{'Contains'}['algorithme'] = "http://www.w3.org/2001/04/xmlenc#sha256";
		$node->{'UnitIdentifier'} = basename($fileName);
		return $node;
	}
	
}