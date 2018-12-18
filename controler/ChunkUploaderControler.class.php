<?php

class ChunkUploaderControler extends PastellControler {

	/**
	 * @throws Exception
	 */
	public function chunkUploadAction(){
		$id_e = $this->getPostOrGetInfo()->getInt('id_e');
		$id_d = $this->getPostOrGetInfo()->get('id_d');
		$id_ce = $this->getPostOrGetInfo()->get('id_ce');
		$field = $this->getPostOrGetInfo()->get('field');
		if ($id_d) {
			$donneesFormulaire = $this->getDonneesFormulaireFactory()->get($id_d);

			$info = $this->getDocument()->getInfo($id_d);

			if ( ! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),$info['type'].":edition",$id_e)) {
				echo "KO";
				exit_wrapper();
			}

		} else if($id_ce) {
			$donneesFormulaire = $this->getDonneesFormulaireFactory()->getConnecteurEntiteFormulaire($id_ce);

			if ( ! $this->getRoleUtilisateur()->hasDroit($this->getId_u(),"entite:edition",$id_e)) {
				echo "KO";
				exit_wrapper();
			}

		} else {
			throw new Exception("id_d ou id_ce est obligatoire");
		}


		$config = new \Flow\Config();
		$config->setTempDir(UPLOAD_CHUNK_DIRECTORY);

		$request = new \Flow\Request();

		$upload_filepath = UPLOAD_CHUNK_DIRECTORY . "/{$id_e}_{$id_d}_{$field}".time()."_".mt_rand(0,mt_getrandmax());

		$this->getLogger()->debug("Chargement partiel du fichier : $upload_filepath dans (id_e={$id_e},id_d={$id_d},id_ce={$id_ce},field={$field}");

		if (\Flow\Basic::save($upload_filepath, $config, $request)) {

			if ($donneesFormulaire->getFormulaire()->getField($field)->isMultiple()){
				$nb_file = $donneesFormulaire->get($field)?count($donneesFormulaire->get($field)):0;
				$this->getLogger()->debug("ajout fichier $nb_file");
				$donneesFormulaire->addFileFromCopy($field, $request->getFileName(), $upload_filepath,$nb_file);
			} else {
				$donneesFormulaire->addFileFromCopy($field, $request->getFileName(), $upload_filepath);
			}
			$this->getLogger()->debug("chargement terminé");
			unlink($upload_filepath);
		}

		if (1 == mt_rand(1, 100)) {
			\Flow\Uploader::pruneChunks(UPLOAD_CHUNK_DIRECTORY);
		}
		echo "OK";
		exit_wrapper();
	}


}