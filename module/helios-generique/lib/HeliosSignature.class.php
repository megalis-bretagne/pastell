<?php

//TODO Cette classe devrait être implémenté dans le connecteur Libersign !!

class HeliosSignature {
	
	private function checkRecetteOrDepense($xml){
		if ($xml->PES_DepenseAller){
			return;
		} 
		if($xml->PES_RecetteAller) {
			return;
		}
		throw new Exception("Le bordereau ne contient ni Depense ni Recette");
	}
		
	private function hasIdOnAllBordereau($xml){
		foreach(array('PES_DepenseAller','PES_RecetteAller') as $tag){
			if (! $xml->$tag){
				continue;
			}
			foreach($xml->$tag->Bordereau as $bordereau){
				if (empty($bordereau['Id'])){
					return false;
				}
			}
		}
		return true;
	}
	
	public function getInfoForSignature($xml_file_path,Libersign $libersign){
		$xml = simplexml_load_file($xml_file_path);

		$this->checkRecetteOrDepense($xml);

		$id = array();
		$hash = array();        
        
		if( $this->hasIdOnAllBordereau($xml) ){
			foreach(array('PES_DepenseAller','PES_RecetteAller') as $tag){
				if (! $xml->$tag){
					continue;
				}
	            foreach($xml->$tag->Bordereau as $bordereau){
	            	$isBordereau = true;
	            	$id[]= strval($bordereau['Id']);
	            	$hash[] = $libersign->getSha1($bordereau->asXML());
	            }
			}
        } else if( isset( $xml['Id'] ) && !empty($xml['Id'] ) ) {
        		$id[]  = strval($xml['Id']);
        		$hash[] = $libersign->getSha1($xml->asXML());
        		$isBordereau = false;
        } else {
			throw new Exception("Le bordereau du fichier PES ne contient pas d'identifiant valide, ni la balise PESAller : signature impossible");
		}
        
		$info = array();
		if($isBordereau) {
			$info['isbordereau'] = true;
			$info['bordereau_hash'] = implode(",",$hash);
			$info['bordereau_id'] = implode(",",$id);
		}
		else {
			$info['isbordereau'] = false;
			$info['flux_hash'] = implode(",",$hash);
			$info['flux_id'] = implode(",",$id);
		}
		return $info;
	}

	public function injectSignature($original_file_path,$signature, $isBordereau){
		
		$all_signature = explode(",",$signature);

		$domDocument = new DOMDocument();
		$domDocument->load($original_file_path);
	
		if( $isBordereau ) {
			$all_bordereau = $domDocument->getElementsByTagName('Bordereau');

			foreach($all_signature as $num_bordereau => $signature) {
				$signature_1 = base64_decode($signature);
				$signatureDOM = new DOMDocument();
				$signatureDOM->loadXML($signature_1);
				$signature = $signatureDOM->firstChild->firstChild;
				$cloned = $signature->cloneNode(TRUE);
				
				$bordereauNode = $all_bordereau->item($num_bordereau);

				$bordereauNode->appendChild($domDocument->importNode($cloned,true));
			}
		}
		else {
			$signature_1 = base64_decode($signature);
			$signatureDOM = new DOMDocument();
			$signatureDOM->loadXML($signature_1);
            $signature = $signatureDOM->firstChild->firstChild;
			
            $rootNode = $domDocument->documentElement;
            $rootNode->appendChild($domDocument->importNode($signature,true));
		}

		return $domDocument->saveXml();		
	}
	
}