<?php

class ActesGeneriqueAutreDocumentAttacheChange extends ActionExecutor {

	public function go(){
		if (! $this->getDonneesFormulaire()->get('type_pj')){
			return true;
		}

		$this->getDonneesFormulaire()->deleteField('type_pj');
		$this->getDonneesFormulaire()->deleteField('type_piece');
		$this->setLastMessage("Modification des fichiers : la typologie a été supprimée");
		return false;
	}

}