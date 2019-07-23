<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'DonneesFormulaireException.php';

/**
 * Gestion des données de formulaire à partir d'un fichier YML de type clé:valeur
 */
class DonneesFormulaire {
		
	private $filePath;
	private $documentType;
	
	private $lastError;
	
	private $onChangeAction;

	private $editable_content;
	private $has_editable_content;
	
	private $isModified;
	
	private $fichierCleValeur;
	
	private $fieldDataList;

	/** @var  DocumentIndexor */
	private $documentIndexor;

	/**
	 * DonneesFormulaire constructor.
	 * @param $filePath string emplacement vers un fichier YML
	 *      			contenant les données du document sous la forme de ligne clé:valeur
	 * @param DocumentType $documentType
	 * @param YMLLoader|null $ymlLoader
	 */
	public function __construct($filePath, DocumentType $documentType, YMLLoader $ymlLoader = null){
		$this->filePath = $filePath;
		$this->documentType = $documentType;
		$this->onChangeAction = array();
		$this->fichierCleValeur = new FichierCleValeur($filePath,$ymlLoader);
		$this->setOnglet();
		/** @var Field $field */
		foreach($this->getFormulaire()->getAllFields() as $field){
			$this->setFieldData($field->getName());
		}
	}
	
	private function setFieldData($fieldName,$ongletNum=-1){
		if (empty($this->fieldDataList[$fieldName])){
			if ($ongletNum != -1){
				$onglet_list = $this->getOngletList();
				$onglet_name = $onglet_list[$ongletNum];
			} else {
				$onglet_name = false;
			}
			
			$field = $this->getFormulaire()->getField($fieldName,$onglet_name);
			if (! $field){
				$field = new Field($fieldName, array());
			}
			$this->fieldDataList[$fieldName] = new FieldData($field, $this->getDisplayValue($field));
		}
	}
	
	public function fieldExists($fieldName){
		return $this->getFormulaire()->getField($fieldName);
	}
	
	private function setNewValueToFieldData($fieldName){
		$field = $this->getFieldData($fieldName)->getField();
		$this->fieldDataList[$fieldName] = new FieldData($field, $this->getDisplayValue($field));
	}
	
	public function setDocumentIndexor(DocumentIndexor $documentIndexor){
		$this->documentIndexor = $documentIndexor;
	}
	
	public function getNbOnglet(){
		if ($this->documentType->isAfficheOneTab()){
			return 1;
		}
		return count($this->getOngletList());
	}
	
	public function getOngletList(){
		$onglet = $this->getFormulaire()->getOngletList();
		$page_condition = $this->documentType->getPageCondition();
		foreach($onglet as $ongletNum => $ongletName){
			if (isset($page_condition[$ongletName])){
				foreach($page_condition[$ongletName] as $field => $value){
					if ($this->fichierCleValeur->get($field) != $value){
						unset($onglet[$ongletNum]);
						continue;
					}
				}
			} 
		}
		return array_values($onglet);
	} 
	
	public function getFieldDataListAllOnglet($my_role){
		$ongletList = $this->getOngletList();
		$fieldsList = array();
		foreach ($ongletList as $onglet_num => $onglet){
			$fieldsList = array_merge($fieldsList,$this->getFieldDataList($my_role,$onglet_num));
		}
		return $fieldsList;
	}
	
	public function getFieldDataList($my_role,$ongletNum = 0){
		$ongletList = $this->getOngletList();
		if (empty($ongletList[$ongletNum])){
			return array();
		}
		$fieldNameList = $this->getFormulaire()->getFieldsForOnglet($ongletList[$ongletNum]);
		return $this->getFieldDataListByFieldName($my_role,$fieldNameList,$ongletNum);
	}
	
	private function getFieldDataListByFieldName($my_role,array $fieldNameList,$ongletNum=-1){
		$result = array();
		foreach ($fieldNameList as $field) {
			if ($field->isShowForRole($my_role)){
				$result[] = $this->getFieldData($field->getName(),$ongletNum);
			}
		}
		return $result;
	}
	
	/**
	 * @param string $fieldName
	 * @param int $ongletNum
	 * @return FieldData
	 */
	public function getFieldData($fieldName,$ongletNum = -1){
		$fieldName  = Field::Canonicalize($fieldName);
		unset($this->fieldDataList[$fieldName]);
		$this->setFieldData($fieldName,$ongletNum);
		return $this->fieldDataList[$fieldName];
	}
	
	private function getDisplayValue(Field $field){
		if (! $field->getProperties('depend')){
			return $this->get($field->getName());
		}
		$cible = $this->get($field->getProperties('depend'));
		if (!$cible){
			$cible = array();
		}
		$value = array();
		foreach($cible as $j => $file){
			$value[$file] = $this->get($field->getName()."_$j");
		}
		return $value;
	}	
	
	/*Fonction pour la construction de l'objet*/
	private function setOnglet(){
		$onglet_to_remove = array();
		$page_condition = $this->documentType->getPageCondition();
		foreach($page_condition as $page => $condition){
			foreach($condition as $field => $value){
				if ($this->get($field) != $value){
					$onglet_to_remove[] = $page;
				}
			}
		}
		$this->getFormulaire()->removeOnglet($onglet_to_remove);
		$this->getFormulaire()->setAfficheOneTab($this->documentType->isAfficheOneTab());
	}
	
	
	
	//C'est un truc qu'on peut récupérer de DocumentType et de l'action en cours
	public function setEditableContent(array $editable_content){
		$this->has_editable_content = true;
		$this->editable_content = $editable_content;
	}
	
	/*Fonctions pour récupérer des objets ou des infos de plus bas niveau*/
	/**
	 * Permet de récupérer l'objet Formulaire configuré vis-à-vis des données de ce DonneesFormulaire
	 * @return Formulaire
	 */
	public function getFormulaire(){
		return $this->documentType->getFormulaire();
	}

	/**
	 * @param $item
	 * @param bool|false $default
	 * @return string | array
	 */
	public function get($item,$default=false){
		$item  = Field::Canonicalize($item);
		if (! $this->fichierCleValeur->exists($item)){
			return $default;
		}
		$value = $this->fichierCleValeur->get($item);
		if (!is_array($value)) {
			if (in_array(strtolower($value), array('true', 'on', '+', 'yes', 'y'))) {
				return true;
			}
	  	 
			if (in_array(strtolower($value), array('false', 'off', '-', 'no', 'n'))) {
				return false;
			}
		}
		return $value;
	}


	/**
	 * @return string contenu du champs déclaré comme titre dans le formulaire
	 */
	public function getTitre(){
		$titre_field = $this->getFormulaire()->getTitreField();
		return $this->get($titre_field);
	}

	/*Fonctions utilisées pour le rendu/l'affichage des données*/

	/**
	 * Indique si le champs est modifiable
	 * 
	 * @param string $field_name
	 * @return boolean
	 */	
	public function isReadOnly($field_name){
		$fieldData = $this->getFieldData($field_name);
		
		$field = $fieldData->getField(); 

		/* Ce n'est pas parce qu'on a un no-show que c'est read-only...*/
		/*if ($field->getProperties('no-show')){
			return true;
		}*/

		$read_only_content = $field->getProperties('read-only-content') ;
		if (!$read_only_content){
			return false;
		}	
		foreach($read_only_content as $key => $value){
			if ($this->get($key) != $value){
				return false;
			}
		}
		return true;
	}
	
	public function isEditable($field_name){
		if ($this->isReadOnly($field_name)){
			return false;
		}
		if ( ! $this->has_editable_content){
			return true;
		}
		return in_array($field_name,$this->editable_content);
	}
	
	
	/*fonction sur l'emplacement et le nom des fichiers annexes*/
	public function getFilePath($field_name,$num = 0){
		return  $this->filePath."_".$field_name."_$num";
	}
	
	/*Fonctions de sauvegarde*/
	public function injectData($fieldName,$fieldValue){
		$this->fichierCleValeur->set($fieldName,$fieldValue);
		$this->getFieldData($fieldName)->setValue($fieldValue);
	}
	
	/**
	 * Permet de sauver tous les champs contenu sur le même onglet. Les champs non renseigné sont mis à vide (sauf les champs de type password)
	 * @param Recuperateur $recuperateur
	 * @param FileUploader $fileUploader
	 * @param int $pageNumber numéro de l'onglet
	 */
	public function saveTab(Recuperateur $recuperateur, FileUploader $fileUploader,$pageNumber){
		$this->isModified = false;
		$this->getFormulaire()->setTabNumber($pageNumber);

		/** @var Field $field */
		foreach ($this->getFormulaire()->getFields() as $field){
			if (! $this->isEditable($field->getName())){
				continue;
			}
			if ($field->getProperties('no-show')){
				continue;
			}
			$type = $field->getType();
				
			if ($type == 'externalData'){
				continue;
			}
			if ( $type == 'file'){
				$this->saveFile($field,$fileUploader);
			} elseif($field->getProperties('depend') && is_array($this->get($field->getProperties('depend')))) {
				foreach($this->get($field->getProperties('depend')) as $i => $file){
					$key_name = $field->getName()."_$i";
					if (! $this->fichierCleValeur->exists($key_name)) {
						$this->fichierCleValeur->set($key_name, false);
					}
					if ($this->fichierCleValeur->get($key_name) != $recuperateur->get($key_name)){
						$this->fichierCleValeur->set($key_name,$recuperateur->get($key_name));
						$this->isModified = true;
					}
				}
			} else {
				$name = $field->getName();
				$value =  $recuperateur->get($name);
	
				if ($type == 'password'){
					$value =  $recuperateur->getNoTrim($name,"");
				}
				if (! $this->fichierCleValeur->exists($name)){
					$this->fichierCleValeur->set($name,"");
				}
	
				if ( ( $this->fichierCleValeur->get($name) != $value) &&  $field->getOnChange()  ){
					if (! in_array($field->getOnChange(),$this->onChangeAction)){
						$this->onChangeAction[] = $field->getOnChange();
					}
				}
	
				if ( ( ($type != 'password' ) || $field->getProperties('may_be_null')  ) ||  $value){
					$this->setInfo($field,$value);
				}
			}
		}
		$this->saveDataFile(false);
	}
	
	private function setInfo(Field $field, $value){
		if ($this->fichierCleValeur->get($field->getName()) === $value){
			return;
		}
		if ($field->getType() == 'date'){
			$value = preg_replace("#^(\d{2})/(\d{2})/(\d{4})$#",'$3-$2-$1',$value);
		}
	
		$this->injectData($field->getName(),$value);
		$this->isModified = true;
	}
	
	public function saveAllFile(FileUploader $fileUploader){
		$allField = $this->getFormulaire()->getAllFieldsDisplayedFirst();
		foreach($fileUploader->getAll() as $filename => $name){
			if (isset($allField[$filename])){
				/** @var Field $field */
				$field = $allField[$filename];
				if (! $this->isEditable($field->getName())){
					continue;
				}
				$this->saveFile($field,$fileUploader);
			}
		}
		if ($this->isModified) {
			$this->saveDataFile(false);
		}
	}
	
	private function saveFile(Field $field, FileUploader $fileUploader){
		$fname = $field->getName();

		if ($fileUploader->getName($fname)){
			$num = $this->fichierCleValeur->count($fname);

			if ($field->isMultiple()){
				for($i=0; $i< $fileUploader->getNbFile($fname); $i++) {
					$this->fichierCleValeur->addValue($fname, $fileUploader->getName($fname,$i));
				}
			} else {
				$this->fichierCleValeur->setMulti($fname,  $fileUploader->getName($fname));

			}

			$this->setFieldData($fname);

			for($i=0; $i < $fileUploader->getNbFile($fname); $i++) {
				$fileUploader->save($fname, $this->getFilePath($fname, $num+$i),$i);
			}
			$this->isModified = true;
			if ($field->getOnChange()){
				$this->onChangeAction[] = $field->getOnChange();
			}
			
		}
	}
	
	public function setData($field_name,$field_value){		
		$this->injectData($field_name,$field_value);
		$this->saveDataFile();		
	}
	
	public function deleteField($fieldName){
		$this->fichierCleValeur->deleteField($fieldName);
		$this->saveDataFile();
	}
	
	public function setTabData(array $field){
		foreach($field as $name => $value){
			$this->injectData($name,$value);
		}
		$this->saveDataFile();
	}

	public function setTabDataVerif(array $input_field){
		$allField = $this->getFormulaire()->getFieldsList();
		foreach($input_field as $field_name => $value){
			if (isset($allField[$field_name])){
				if (! $this->isEditable($field_name) ){
					continue;
				}
				$this->injectData($field_name,$value);
				$this->isModified = true;
				/** @var Field $field */
				$field = $allField[$field_name];
				if ($field->getOnChange()){
					$this->onChangeAction[] = $field->getOnChange();
				}
			}
		}
		/**
		 * @var string $field_name
		 * @var  Field $field
		 */
		foreach($allField as $field_name=>$field){
			if($field->getProperties('depend') &&
			is_array($this->get($field->getProperties('depend')))) {
				foreach($this->get($field->getProperties('depend')) as $i => $file){
					if (isset($input_field[$field_name."_$i"])){
						$this->injectData($field_name."_$i",$input_field[$field_name."_$i"]);
						$this->isModified = true;
					}
				}
			}
		}
		$this->saveDataFile(false);
	}

	/**
	 * @param $field_name
	 * @param $file_name
	 * @param $raw_data
	 * @param int $file_num
	 * @throws Exception
	 */
	public function addFileFromData($field_name,$file_name,$raw_data,$file_num = 0){
		$this->fichierCleValeur->setMulti($field_name, $file_name,$file_num);
		$file_path = $this->getFilePath($field_name,$file_num);
		$result = file_put_contents($file_path,$raw_data);
		if ($result === false){
			throw new Exception("Impossible d'écrire dans le fichier $file_path");
		}
		$this->setNewValueToFieldData($field_name);		
		$this->saveDataFile();

		$allField = $this->getFormulaire()->getFieldsList();
		if (isset($allField[$field_name])) {
			$field = $allField[$field_name];
			if ($field->getOnChange()) {
				$this->onChangeAction[] = $field->getOnChange();
			}
		}
	}
	
	public function addFileFromCopy($field_name,$file_name,$file_source_path,$file_num=0){
		$this->fichierCleValeur->setMulti($field_name, $file_name,$file_num);
		copy($file_source_path,$this->getFilePath($field_name,$file_num));
		$this->setNewValueToFieldData($field_name);
		$this->saveDataFile();
		$this->isModified = true;

		$allField = $this->getFormulaire()->getFieldsList();
		if (isset($allField[$field_name])) {
			$field = $allField[$field_name];
			if ($field->getOnChange()) {
				$this->onChangeAction[] = $field->getOnChange();
			}
		}
	}
	
	public function removeFile($fieldName,$num = 0){
	    if (! file_exists($this->getFilePath($fieldName,$num))){
	        return;
        }
		unlink($this->getFilePath($fieldName,$num));
		for($i = $num + 1; $i < $this->fichierCleValeur->count($fieldName) ; $i++){
			rename($this->getFilePath($fieldName,$i),$this->getFilePath($fieldName,$i - 1));
		}
		$this->fichierCleValeur->delete($fieldName, $num);

		$field = $this->getFieldData($fieldName)->getField();
		if ($field->getOnChange()){
			$this->onChangeAction[] = $field->getOnChange();
		}
		$this->isModified = true;
		$this->saveDataFile(false);
	}
	
	private function saveDataFile($setModifiedToFalse = true){
		$this->fichierCleValeur->save();
		if ($setModifiedToFalse) {
			$this->isModified=false;
		}
		$this->updateAllIndexedField();
		$this->setOnglet();
	}
	
	private function updateAllIndexedField(){
		if (! $this->documentIndexor){
			return;
		}
		if (empty($this->fieldDataList)){
			return;
		}
		/**
		 * @var string $fieldName
		 * @var FieldData $fieldData
		 */

		$all_index = $this->documentIndexor->getAllIndex();
		foreach($this->fieldDataList as $fieldName => $fieldData){
			if (! isset($all_index[$fieldName]) || $all_index[$fieldName] != $fieldData->getValueForIndex()){
				$this->updateIndexedField($fieldData);
			}
		}
	}
	
	private function updateIndexedField(FieldData $fieldData){
		if ( ! $fieldData->getField()->isIndexed()){
			return;
		}
		$value = $fieldData->getValueForIndex(); 
		$this->documentIndexor->index($fieldData->getField()->getName(), $value);
	}
	
	/*Fonctions permettant de savoir si il y a eu des choses modifiés après la sauvegarde*/
	public function isModified(){
		return $this->isModified;
	}
	
	public function getOnChangeAction(){
		return $this->onChangeAction;
	}
	
	/*Fonction de récupération de valeur*/
	public function getFileContent($field_name,$num=0){
		$file_path = $this->getFilePath($field_name,$num);
		if (! is_readable($file_path)){
			$this->lastError = "Le fichier $file_path ne peut pas être lu";
			return false;
		}
		return file_get_contents($file_path);
	}
	
	//http://stackoverflow.com/questions/6595183/docx-file-type-in-php-finfo-file-is-application-zip
	private function getOpenXMLMimeType($file_name){
		$ext = pathinfo($file_name,PATHINFO_EXTENSION);
		$openXMLExtension = array(
				'xlsx' => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
				'xltx' => "application/vnd.openxmlformats-officedocument.spreadsheetml.template",
				'potx' =>  "application/vnd.openxmlformats-officedocument.presentationml.template",
				'ppsx' =>  "application/vnd.openxmlformats-officedocument.presentationml.slideshow",
				'pptx'   =>  "application/vnd.openxmlformats-officedocument.presentationml.presentation",
				'sldx'   =>  "application/vnd.openxmlformats-officedocument.presentationml.slide",
				'docx'   =>  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
				'dotx'   =>  "application/vnd.openxmlformats-officedocument.wordprocessingml.template",
				'xlam'   =>  "application/vnd.ms-excel.addin.macroEnabled.12",
				'xlsb'   =>  "application/vnd.ms-excel.sheet.binary.macroEnabled.12");
		if (isset($openXMLExtension[$ext])){
			return $openXMLExtension[$ext];
		}
		return false;
	}
	
	public function getContentType($field_name,$num  = 0){
		$file_path = $this->getFilePath($field_name,$num);
		if (! file_exists($file_path)){
			return false;
		}

		$fileInfo = new finfo();
		$result = $fileInfo->file($file_path,FILEINFO_MIME_TYPE);
		
		if ($result == 'application/zip'){
			$file_name = $this->getFileName($field_name,$num);
			$result = $this->getOpenXMLMimeType($file_name)?:'application/zip';
		}

        /**
         * php 7.2, file_info renvoi "text/xml" à la place de "application/xml
         * @see https://bugs.php.net/bug.php?id=75380
         */
		if ($result == 'text/xml'){
		    $result = 'application/xml';
        }
		
		return $result;
	}

	public function getFileNumber($field){
		if (! $this->get($field)){
			return 0;
		}
		return count($this->get($field));
	}

	public function getFileName($field_name,$num = 0){
		$all_file_name = $this->get($field_name);
		return 	$all_file_name[$num];
	}

	public function getFileNameWithoutExtension($field_name,$num = 0){
		$file_name = $this->getFileName($field_name,$num);
		return pathinfo($file_name,PATHINFO_FILENAME);
	}

	public function getWithDefault($item){
		$default = $this->getFormulaire()->getField($item)->getDefault();
		$result = $this->get($item,$default);
		return $result ?: $default;
	}
	
	public function geth($item,$default = false){
		return nl2br(htmlentities($this->get($item,$default),ENT_QUOTES,"UTF-8"));
	}

	public function isValidable(){
        $totalFileSize = 0;
        $fileSizesByField = [];

        /** @var FieldData $fieldData */
		foreach($this->getFieldDataListAllOnglet(false) as $fieldData) {
			if (! $fieldData->isValide()) {
				$this->lastError = $fieldData->getLastError();
				return false;
			}
			/** @var Field $field */
			$field = $fieldData->getField();
			if ($field->getProperties('is_equal')){
				if ($this->get($field->getProperties('is_equal')) != $this->get($field->getName())){
					$this->lastError =$field->getProperties('is_equal_error');
					return false;
				}
			}
			if ($field->getProperties('content-type')){
				$ctype = $this->getContentType($field->getName(),0);
				if ($ctype && $ctype != $field->getProperties('content-type')){
					$this->lastError = "Le fichier «{$field->getLibelle()}» n'est pas un fichier {$field->getProperties('content-type')} ($ctype trouvé)";
					return false;
				}
			}
            if ($field->getType() === 'file' && $this->get($field->getName()) && $field->isMultiple()) {
                try {
                    $fileSizesByField[$field->getName()] = $this->getMultipleFileSize($field);
                } catch (DonneesFormulaireException $e) {
                    $this->lastError = $e->getMessage();
                    return false;
                }
            }
            if ($field->getType() === 'file' && $this->get($field->getName()) && !$field->isMultiple()) {
                try {
                    $fileSizesByField[$field->getName()] = $this->getFileSize($field);
                } catch (DonneesFormulaireException $e) {
                    $this->lastError = $e->getMessage();
                    return false;
                }
            }
        }

        if ($this->documentType->getThresholdFields()) {
            foreach ($this->documentType->getThresholdFields() as $fieldListed) {
                $totalFileSize += $fileSizesByField[$fieldListed] ?? 0;
            }
        } else {
            $totalFileSize = array_sum($fileSizesByField);
        }
        $threshold = $this->documentType->getThresholdSize();
        if ($threshold && $totalFileSize > $threshold) {
            $this->lastError = "L'ensemble des fichiers dépasse le poids limite autorisé : $threshold octets, ($totalFileSize trouvé)";
            return false;
        }

        return true;
	}
	
	public function getLastError(){
		return $this->lastError;
	}
	
	public function delete(){
		$file_to_delete = glob($this->filePath."*");
		foreach($file_to_delete as $file){
			unlink($file);
		}
	}
	
	public function getRawData(){
		return $this->fichierCleValeur->getInfo();
	}

	public function getRawDataWithoutPassword(){
		$result = $this->getRawData()??[];
		foreach($result as $element_id => $value){
		    $field = $this->getFormulaire()->getField($element_id);
		    if (empty($field)){
                continue;
            }
			if ($field->getType() == 'password'){
				$result[$element_id] = "MOT DE PASSE NON RECUPERABLE";
			}
		}
		return $result;
	}


	public function getMetaData(){
		return file_get_contents($this->filePath);
	}
	
	public function getAllFile(){
		$result = array();
		/** @var Field $field */
		foreach($this->getFormulaire()->getAllFields() as $field){
			if ($field->getType() != 'file'){
				continue;
			}
			if (! $this->get($field->getName())){
				continue;
			}
			$result[] = $field->getName();
		}
		return $result;
	}
	
	public function extensionByMimeType($file_path,$file_name) {
		$path_parts = pathinfo($file_name);
		
		$fileInfo = new finfo();
		$contentType = $fileInfo->file($file_path,FILEINFO_MIME_TYPE);
				
		$map = array(
				'application/pdf'   => '.pdf',
				'application/zip'   => '.zip',
				'application/xml'   => '.xml',
				'image/gif'         => '.gif',
				'image/jpeg'        => '.jpg',
				'image/png'         => '.png',
				'text/css'          => '.css',
				'text/html'         => '.html',
				'text/javascript'   => '.js',
				'text/plain'        => '.txt',
				'text/xml'          => '.xml',
		);
		$result = "";
		
		if (isset($map[$contentType])) {
			$result = $map[$contentType];
		}
	
		if ($result == ".zip"){
			if (in_array($path_parts['extension'],array('xltx','potx','ppsx','sldx','docx','dotx','xlam','xlsb'))){
				return ".".$path_parts['extension'];
			}
		}
		if ($result =='.txt'){
			$file_content = file_get_contents($file_path);
			if (preg_match("#-----BEGIN PKCS7-----#", $file_content)){
				return ".p7c";
			}
		}
		
		if (!$result){
			if (! empty($path_parts['extension'])) {
				$result = ".".$path_parts['extension']; 
			}
		}
		
		return $result;
	}
	
	private function renameFilename($file_path,$new_filename){
		$path_parts = pathinfo($file_path);
		return $path_parts['dirname'] . DIRECTORY_SEPARATOR .$new_filename;
	}

	/**
	 * @param $field_name
	 * @param $folder_destination
	 * @param int $num
	 * @param bool $new_filename sans l'extension !
	 * @return bool|string
	 */
	public function copyFile($field_name,$folder_destination,$num = 0,$new_filename = false){
		$file_name = $this->get($field_name);
		$file_name = $file_name[$num];
		$file_path = $this->getFilePath($field_name,$num);
		if (! file_exists($file_path)){
			return false;
		}
		
		$destination = "$folder_destination/$file_name";
		if ($new_filename){
			$extension = $this->extensionByMimeType($file_path,$file_name);				
			$destination = $this->renameFilename($destination, $new_filename.$extension);
		}
		copy($file_path,$destination);
		return $destination;
	}
	
	public function copyAllFiles($field_name,$folder_destination,$new_filename = false){
		$result = array();
		if (!$this->get($field_name) ){
			return $result;
		}
		foreach($this->get($field_name) as $i => $file_name){
			$destination = $new_filename?$new_filename."-".$i:false;
			$result[] = $this->copyFile($field_name, $folder_destination,$i,$destination);
		}
		return $result;
	}

	public function jsonExport(){
		$result['metadata'] = $this->getRawData();
		foreach($this->getAllFile() as $field){
			foreach($this->get($field) as $file_num => $file_name){
				$result['file'][$field][$file_num] = base64_encode($this->getFileContent($field,$file_num));
			}
		}
		return json_encode($result);
	}

	/**
	 * @param $data
	 * @throws Exception
	 */
	public function jsonImport($data){
		$result = json_decode($data,true);
		if ($result === null){
			throw new Exception("Impossible de déchiffrer le fichier : erreur " . json_last_error());
		}
		if (! isset($result['metadata'])){
			throw new Exception("Clé metadata absente du fichier");
		}

		foreach($result['metadata'] as $field_name => $field_value){
			if (! is_array($field_value)) {
				$this->setData($field_name, $field_value);
			} else {
				foreach($field_value as $file_num => $file_name){
					$file_content = "";
					if (! empty($result['file'][$field_name][$file_num])){
						$file_content = $result['file'][$field_name][$file_num];
						$file_content = base64_decode($file_content,true);
					}
					$this->addFileFromData($field_name,$file_name,$file_content,$file_num);
				}
			}
		}
	}

    /**
     * @param Field $field
     * @return int
     * @throws DonneesFormulaireException
     */
    private function getMultipleFileSize(Field $field)
    {
        $multipleFileSize = 0;
        for ($i = 0; $i < $this->getFileNumber($field->getName()); ++$i) {
            $multipleFileSize += $this->getFileSize($field, $i);
        }
        if ($field->getMaxMultipleFileSize() && $multipleFileSize > $field->getMaxMultipleFileSize()) {
            throw new DonneesFormulaireException(
                "L'ensemble des fichiers du champ multiple «{$field->getLibelle()}» dépasse le poids limite autorisé : ({$field->getMaxMultipleFileSize()})  octets, ($multipleFileSize trouvé)"
            );
        }

        return $multipleFileSize;
    }

    /**
     * @param Field $field
     * @param int $fileNumber
     * @return int
     * @throws DonneesFormulaireException
     */
    private function getFileSize(Field $field, $fileNumber = 0)
    {
        $filesize = filesize($this->getFilePath($field->getName(), $fileNumber));
        $filename = $this->getFileName($field->getName(), $fileNumber);
        if ($field->getMaxFileSize() && $filesize > $field->getMaxFileSize()) {
            throw new DonneesFormulaireException(
                "Le fichier «{$filename}» ({$field->getLibelle()}) dépasse le poids limite autorisé : {$field->getMaxFileSize()}  octets, ($filesize trouvé)"
            );
        }

        return $filesize;
    }
}