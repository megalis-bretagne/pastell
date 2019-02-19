<?php

trait DocumentTestCreator {

	/**
	 * @return int id_d
	 * @throws Exception
	 */
	protected function createTestDocument(){
		$document = $this->getObjectInstancier()->getInstance(Document::class);
		$id_d = $document->getNewId();
		$document->save($id_d,"document-type-test");
		$documentEntite = $this->getObjectInstancier()->getInstance(DocumentEntite::class);
		$documentEntite->addRole($id_d,PastellTestCase::ID_E_COL,"editeur");
		return $id_d;
	}

	/**
	 * @return ObjectInstancier
	 */
	abstract public function getObjectInstancier();

}