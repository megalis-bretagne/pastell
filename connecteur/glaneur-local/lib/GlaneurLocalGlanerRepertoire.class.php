<?php

class GlaneurLocalGlanerRepertoire {

	private $glaneurLocalDocumentCreator;
	private $connecteurConfig;
	private $last_message;
	private $id_e;
	private $documentTypeFactory;

	public function __construct(
		GlaneurLocalDocumentCreator $glaneurLocalDocumentCreator,
		DonneesFormulaire $connecteurConfig,
		$id_e,
		DocumentTypeFactory $documentTypeFactory
	) {
		$this->glaneurLocalDocumentCreator = $glaneurLocalDocumentCreator;
		$this->connecteurConfig = $connecteurConfig;
		$this->id_e = $id_e;
		$this->documentTypeFactory = $documentTypeFactory;
	}

	/**
	 *
	 * Retourne le dernier message
	 * @return string
	 */
	public function getLastMessage(){
		return $this->last_message;
	}

	/**
	 * Cette fonction sert à glaner un repertoire contenant directement les fichiers qui seront mis dans un document Pastell
	 *
	 * @param string $repertoire
	 * @return int|bool $id_d si c'est réussi, false sinon
	 * @throws Exception
	 */
	public function glanerRepertoire(string $repertoire){
		if (!$repertoire){
			$this->last_message[] = "Le répertoire ".$repertoire." est vide";
			return false;
		}
		// Le mode manifeste à précédence sur le mode filename_matcher
		if ($this->connecteurConfig->get(GlaneurLocal::MANIFEST_TYPE) == GlaneurLocal::MANIFEST_TYPE_XML) {
			$glaneurLocalDocumentInfo = $this->glanerModeManifest($repertoire);
		} else {
			$glaneurLocalDocumentInfo = $this->glanerModeFilematcher($repertoire);
		}
		return $this->createDocument($glaneurLocalDocumentInfo,$repertoire);
	}

	/**
	 * @param GlaneurLocalDocumentInfo $glaneurLocalDocumentInfo
	 * @param string $repertoire
	 * @return string
	 * @throws Exception
	 */
	private function createDocument(GlaneurLocalDocumentInfo $glaneurLocalDocumentInfo,string $repertoire){
		$id_d = $this->glaneurLocalDocumentCreator->create($glaneurLocalDocumentInfo,$repertoire);
		$this->last_message[] = "Création du document $id_d";
		return $id_d;
	}

	/**
	 * @param $repertoire
	 * @return GlaneurLocalDocumentInfo
	 * @throws Exception
	 */
	private function glanerModeFilematcher($repertoire){
		$file_match = $this->getFileMatch($repertoire);
		$glaneurLocalDocumentInfo = new GlaneurLocalDocumentInfo($this->id_e);
		$glaneurLocalDocumentInfo->nom_flux = $this->connecteurConfig->get(GlaneurLocal::FLUX_NAME);
		$glaneurLocalDocumentInfo->element_files_association = $file_match['file_match'];
		$glaneurLocalDocumentInfo->metadata = $this->getMetadataStatic($file_match);
		$glaneurLocalDocumentInfo->action_ok = $this->connecteurConfig->get(GlaneurLocal::ACTION_OK);
		$glaneurLocalDocumentInfo->action_ko = $this->connecteurConfig->get(GlaneurLocal::ACTION_KO);
		return $glaneurLocalDocumentInfo;
	}

	/**
	 * @param $repertoire
	 * @return array
	 * @throws Exception
	 * @throws UnrecoverableException
	 */
	public  function getFileMatch($repertoire){
		$nom_flux = $this->connecteurConfig->get(GlaneurLocal::FLUX_NAME);
		if (!$nom_flux){
			throw new UnrecoverableException("Impossible de trouver le nom du flux à créer");
		}

		$glaneurLocalFilenameMatcher = new GlaneurLocalFilenameMatcher();
		return $glaneurLocalFilenameMatcher->getFilenameMatching(
			$this->connecteurConfig->get(GlaneurLocal::FILE_PREG_MATCH),
			$this->getCardinalite($nom_flux),
			$this->getFileList($repertoire)
		);
	}

	/**
	 * @param $file_match
	 * @return array
	 * @throws Exception
	 */
	private function getMetadataStatic(array $file_match){
		$metadata_static = $this->connecteurConfig->get(GlaneurLocal::METADATA_STATIC);
		$metadata = array();
		foreach(explode("\n",$metadata_static) as $line){
			$r = explode(':',$line);
			if (count($r)<2){
				continue;
			}
			$key = trim($r[0]);
			$value = trim($r[1]);

			if (preg_match("#^%(.*)%$#",$value,$matches)){
				if (empty($file_match['file_match'][$matches[1]][0])){
					throw new Exception("$matches[1] n'a pas été trouvé dans la correspondance des fichiers");
				}
				$value = $file_match['file_match'][$matches[1]][0];
			}
			else {
				$matches = $file_match['matches'];
				$value = preg_replace_callback(
					'#\$matches\[(\d+)\]\[(\d+)\]#',
					function ($m) use ($matches){
						if (empty($matches[$m[1]][$m[2]])){
							return false;
						}
						return $matches[$m[1]][$m[2]];
					},
					$value
				);
			}

			$metadata[$key] = $value;
		}
		return $metadata;
	}

	private $cardinalite = null;

	/**
	 * @param $type_document
	 * @return array
	 * @throws Exception
	 */
	private function getCardinalite($type_document){
		if ($this->cardinalite === null){
			$documentType = $this->documentTypeFactory->getFluxDocumentType($type_document);

			if (! $documentType->exists()){
				throw new UnrecoverableException("Impossible de trouver le type $type_document sur ce pastell");
			}
			$cardinalite = array();
			foreach($documentType->getFormulaire()->getAllFields() as $field){
				if ($field->getType() == 'file'){
					$cardinalite[$field->getName()] = $field->getProperties('multiple')?'n':'1';
				}
			}
			$this->cardinalite = $cardinalite;
		}

		return $this->cardinalite;
	}



	private function getFileList(string $directory){
		$result = array();
		foreach(new DirectoryIterator($directory) as $file){
			if ($file->isFile()) {
				$result[] = $file->getFilename();
			}
		};
		sort($result);
		return $result;
	}

	/**
	 * @return  GlaneurLocalDocumentInfo
	 * @param $repertoire
	 * @throws Exception
	 */
	private function glanerModeManifest($repertoire) {

		$glaneurLocalDocumentInfo = new GlaneurLocalDocumentInfo($this->id_e);


		$glaneurLocalDocumentInfo->action_ok = $this->connecteurConfig->get(GlaneurLocal::ACTION_OK);
		$glaneurLocalDocumentInfo->action_ko = $this->connecteurConfig->get(GlaneurLocal::ACTION_KO);

		$manifest_filename = $this->connecteurConfig->get(GlaneurLocal::MANIFEST_FILENAME)?:GlaneurLocal::MANIFEST_FILENAME_DEFAULT;
		if (! file_exists($repertoire."/".$manifest_filename)){
			throw new Exception("Le fichier $manifest_filename n'existe pas");
		}

		$simpleXMLWrapper = new SimpleXMLWrapper();

		$xml = $simpleXMLWrapper->loadFile($repertoire."/".$manifest_filename);

		if (empty($xml->attributes()->{'type'})) {
			throw new Exception("Le type de flux n'a pas été trouvé dans le manifest");
		}

		$glaneurLocalDocumentInfo->nom_flux = strval($xml->attributes()->{'type'});
		foreach($xml->{'data'} as $data){
			$name = strval($data['name']);
			$value = strval($data['value']);
			$glaneurLocalDocumentInfo->metadata[$name] = $value;
		}

		foreach($xml->{'files'} as $files){
			$name = strval($files['name']);
			foreach($files->{'file'} as  $file){
				$filename = strval($file['content']);
				if (! file_exists($repertoire."/".$filename)){
					throw new Exception("Le fichier $filename n'a pas été trouvé.");
				}
				$glaneurLocalDocumentInfo->element_files_association[$name][] = $filename;
			}
		}

		return $glaneurLocalDocumentInfo;
	}
}