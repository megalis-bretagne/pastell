<?php
class FakeSAE extends SAEConnecteur {

    const CONNECTEUR_ID = 'fakeSAE';

	private $tmpFile;

	/** @var DonneesFormulaire */
	private $collectiviteProperties;
	
	public function __construct(TmpFile $tmpFile){
		$this->tmpFile = $tmpFile;
	}
	
	public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties){
		$this->collectiviteProperties = $collectiviteProperties;
	}

	/**
	 * @param $bordereauSEDA
	 * @param $archivePath
	 * @param string $file_type
	 * @param string $archive_file_name
	 * @return bool
	 * @throws Exception
	 */
	public function sendArchive($bordereauSEDA,$archivePath,$file_type="TARGZ",$archive_file_name="archive.tar.gz"){
		$this->collectiviteProperties->addFileFromData('last_bordereau', 'bordereau_seda.xml', $bordereauSEDA);
		$this->collectiviteProperties->addFileFromData('last_file', 'donnes.zip', file_get_contents($archivePath));
		if ($this->collectiviteProperties->get('result_send') == 2){
			throw new Exception("Ce connecteur bouchon est configuré pour renvoyer une erreur");
		}
		if ($this->collectiviteProperties->get('result_send') == 3){
			header("Content-type: text/xml");
			echo $bordereauSEDA;
			exit;
		}
		return true;
	}
	
	public function getAcuseReception($id_transfert){
		$simpleXMLWrapper = new SimpleXMLWrapper();
		$xml = $simpleXMLWrapper->loadFile(__DIR__."/fixtures/ACK.xml");
		$xml->{'Date'} = date("c");
		$xml->{'MessageReceivedIdentifier'} = "$id_transfert";
		$xml->{'AcknowledgementIdentifier'}  = "ACK_".mt_rand(0,mt_getrandmax());
		return $xml->asXML();
	}

	/**
	 * @param $id_transfert
	 * @return mixed
	 * @throws SimpleXMLWrapperException
	 */
	protected function getATR($id_transfert){
		$simpleXMLWrapper = new SimpleXMLWrapper();
		$xml = $simpleXMLWrapper->loadFile(__DIR__."/fixtures/ATR.xml");
		$xml->{'Date'} = date("c");
		$xml->{'TransferIdentifier'} = "$id_transfert";
		$xml->{'TransferReplyIdentifier'}  = "ATR_".mt_rand(0,mt_getrandmax());
		$xml->{'Archive'}->{'ArchivalAgencyArchiveIdentifier'} = mt_rand(0,mt_getrandmax());
		return $xml->asXML();


	}
	
	public function getReply($id_transfer){

		$result_verif = $this->collectiviteProperties->get('result_verif')?:1;

		if ($result_verif == 1 ) {
			return $this->getATR($id_transfer);
		}
		if ($result_verif == 2 ) {
			return "<nope><foo></foo></nope>";
		}
		if ($result_verif == 3){
			throw new UnrecoverableException("Impossible de lire le message");
		}
	}
	
	public function getURL($cote){
		return "http://www.libriciel.fr";
	}
	
	public function generateArchive($bordereau,$tmp_folder){
		
		$fileName = $this->tmpFile->create().".zip";
		
		$zip = new ZipArchive;
		
		if (! $zip->open($fileName,ZIPARCHIVE::CREATE)) {
			throw new UnrecoverableException("Impossible de créer le fichier d'archive : $fileName");
		}
		$has_file = false;
		foreach(scandir($tmp_folder) as $fileToAdd) {
			if (is_file("$tmp_folder/$fileToAdd")) {
				$zip->addFile("$tmp_folder/$fileToAdd", $fileToAdd);
				$has_file = true;
			}
		}

		if (! $has_file){
			file_put_contents("$tmp_folder/empty","");
			$zip->addFile("$tmp_folder/empty", "empty");
		}
		$zip->close();
		return $fileName;
		
	}	
	
	public function getErrorString($number){
		
	}

    public function getLastErrorCode(){

    }
	
}