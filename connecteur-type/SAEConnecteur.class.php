<?php 

abstract class SAEConnecteur extends  Connecteur {
	
	public function generateArchive($bordereau,$tmp_folder){
		
		$xml = simplexml_load_string($bordereau);
		foreach($xml->Integrity as $file){
			$file_to_add[] = strval($file->UnitIdentifier);
		}
		$fileName = uniqid()."_archive.tar.gz";
		$command = "tar cvzf $tmp_folder/$fileName --directory $tmp_folder \"" . implode("\" \"",$file_to_add) ."\"";
		$status = exec($command,$output,$return_var);
				
		if ( $return_var != 0) {
			throw new Exception("Impossible de cr�er le fichier d'archive $fileName - status : $return_var");
		}
		
		return $tmp_folder."/$fileName";
	}	
	
	public function getTransferId($bordereau){
		$xml = simplexml_load_string($bordereau);
		return strval($xml->TransferIdentifier);
	}
	
	
	
	abstract public function sendArchive($bordereauSEDA,$archivePath,$file_type="TARGZ",$archive_file_name="archive.tar.gz");
		
	abstract public function getAcuseReception($id_transfert);
	
	abstract public function getReply($id_transfer);
	
	abstract public function getURL($cote);
	
	abstract public function getErrorString($number);
	
	
}