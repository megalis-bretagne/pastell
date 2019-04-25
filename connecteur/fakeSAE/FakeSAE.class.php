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
		return "<test/>";
	}	
	
	
	public function getReply($id_transfer){
		return "<ArchiveTransferAcceptance><Archive><ArchivalAgencyArchiveIdentifier>http://www.libriciel.fr</ArchivalAgencyArchiveIdentifier></Archive></ArchiveTransferAcceptance>";
	}
	
	public function getURL($cote){
		return "http://www.libriciel.fr";
	}
	
	public function generateArchive($bordereau,$tmp_folder){
		
		$fileName = $this->tmpFile->create().".zip";
		
		$zip = new ZipArchive;
		
		if (! $zip->open($fileName,ZIPARCHIVE::CREATE)) {
			throw new Exception("Impossible de créer le fichier d'archive : $fileName");
		}
		foreach(scandir($tmp_folder) as $fileToAdd) {
			if (is_file("$tmp_folder/$fileToAdd")) {
				$zip->addFile("$tmp_folder/$fileToAdd", $fileToAdd);
			}
		}
		$zip->close();
		return $fileName;
		
	}	
	
	public function getErrorString($number){
		
	}

    public function getLastErrorCode(){

    }
	
}