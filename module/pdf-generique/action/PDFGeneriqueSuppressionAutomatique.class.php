<?php

class PDFGeneriqueSuppressionAutomatique extends ActionExecutor {

	public function go(){
		/** @var PdfGeneriqueSuppressionConnecteur $pdfGeneriqueSuppression */
		$pdfGeneriqueSuppression = $this->getConnecteur('pdf-generique-suppression');
		if (! $pdfGeneriqueSuppression){
			throw new Exception("Aucun connecteur pdf-generique-suppression");
		}
		$info = $this->getDocument()->getInfo($this->id_d);

		if ($pdfGeneriqueSuppression->canDelete($info['modification'])){
			$this->setLastMessage("Le document va être supprimé...");
			$this->getActionCreator()->addAction($this->id_e,$this->id_u,"suppression_ok","suppression du document");
			return true;
		}
		$this->setLastMessage("Il n'est pas encore temps de supprimer le document");
		return true;
	}

}