<?php

class XMLFormattage {

	public function format($filename){
		$domDocument = new DOMDocument();
		libxml_use_internal_errors(true);
		$domDocument->preserveWhiteSpace = false;
		$domDocument->formatOutput = true;

		if (! $domDocument->load($filename)){
			throw new Exception("Impossible de charger le XML depuis $filename");
		}

		if (! $domDocument->save($filename)){
			throw new Exception("Impossible de sauvegarder le fichier $filename");
		}
		return true;
	}
}