<?php

require_once(__DIR__."/lib/XMLFile.class.php");
require_once(__DIR__."/lib/AgapeFile.class.php");
require_once(__DIR__."/lib/RelaxNG.class.php");
require_once(__DIR__."/lib/FluxData.class.php");
require_once(__DIR__."/lib/FluxDataTest.class.php");
require_once(__DIR__."/lib/FluxDataStandard.class.php");

require_once(__DIR__."/lib/RelaxNgImportAgapeAnnotation.class.php");
require_once(__DIR__."/lib/GenerateXMLFromAnnotedRelaxNG.class.php");

require_once(__DIR__."/lib/AnnotationWrapper.class.php");
require_once(__DIR__."/lib/GenerateBordereauSEDA.class.php");
require_once(__DIR__."/lib/GenerateXMLFromRelaxNg.class.php");
require_once(__DIR__."/lib/SedaValidation.class.php");
require_once(__DIR__."/lib/XMLCleaningEmptyNode.class.php");



class SedaNG extends SEDAConnecteur {

	/** @var  DonneesFormulaire */
	private $connecteurConfig;

	private $last_validation_error;

	/** @var  FluxData */
	private $fluxData;


	public function setConnecteurConfig(DonneesFormulaire $connecteurConfig) {
		$this->connecteurConfig = $connecteurConfig;
	}

	public function getLastValidationError(){
		return $this->last_validation_error ;
	}

	private function getTransferIdentifier(){
		$last_date = $this->connecteurConfig->get("date_dernier_transfert");
		$numero_transfert = $this->connecteurConfig->get("dernier_numero_transfert");

		$date = date('Y-m-d');
		if ($last_date == $date){
			$numero_transfert ++;
		} else {
			$numero_transfert = 1;
		}

		$this->connecteurConfig->setData('date_dernier_transfert', $date);
		$this->connecteurConfig->setData('dernier_numero_transfert', $numero_transfert);

		return $date ."-".$numero_transfert;
	}

	public function getBordereauTest(){
		$flux_info = $this->connecteurConfig->getFileContent('flux_info_content');
		$data = array();
		if($flux_info){
			foreach(json_decode($flux_info,true) as $key => $value){
				$data[$key] = $value;
			}
		}

		$fluxDataTest = new FluxDataTest($data);

		return $this->getBordereauNG($fluxDataTest);
	}

	public function setFluxData(FluxData $fluxData){
		$this->fluxData = $fluxData;
	}

	public function getBordereau(array $transactionInfo){
		if (! $this->fluxData){
			throw new Exception("Le connecteur SEDA NG n'est pas supporté par ce flux...");
		}
		return $this->getBordereauNG($this->fluxData);
	}

	public function getBordereauNG(FluxData $fluxData){

        $relax_ng_path = $this->getSchemaRngPath();
        $agape_file_path = $this->getAgapeFilePath();

		$relaxNGImportAgapeAnnotation = new RelaxNgImportAgapeAnnotation();
		$relaxNG_with_annotation = $relaxNGImportAgapeAnnotation->importAnnotation($relax_ng_path, $agape_file_path);


		$generateXMLFromAnnotedRelaxNG = new GenerateXMLFromAnnotedRelaxNG(new RelaxNG());
		$bordereau_seda_with_annotation = $generateXMLFromAnnotedRelaxNG->generateFromRelaxNGString($relaxNG_with_annotation);

		$connecteur_info = $this->connecteurConfig->getFileContent('connecteur_info_content');
		$data = array();
		if($connecteur_info){
			foreach(json_decode($connecteur_info,true) as $key => $value){
				$data[$key] = $value;
			}
		}

		$annotationWrapper = new AnnotationWrapper();
		$annotationWrapper->setConnecteurInfo($data);
		$annotationWrapper->setFluxData($fluxData);
		
		$annotationWrapper->setCompteurJour($this->getTransferIdentifier());
		$generateBordereauSEDA = new GenerateBordereauSEDA();
		$xml = $generateBordereauSEDA->generate($bordereau_seda_with_annotation, $annotationWrapper);

		return $xml;
	}

	private function getSchemaRngPath(){
        $relax_ng_path = $this->connecteurConfig->getFilePath('schema_rng');
        if (! file_exists($relax_ng_path)){
            throw new Exception("Le profil SEDA (RelaxNG) n'a pas été trouvé.");
        }
        return $relax_ng_path;
    }

    private function getAgapeFilePath(){
        $agape_file_path = $this->connecteurConfig->getFilePath('profil_agape');

        if (! file_exists($agape_file_path)){
            throw new Exception("Le profil SEDA (fichier Agape) n'a pas été trouvé.");
        }
        return $agape_file_path;
    }

	public function validateBordereau($bordereau_content){
		$relax_ng_path = $this->getSchemaRngPath();
		$sedaValidation = new SedaValidation();
		if (! $sedaValidation->validateRelaxNG($bordereau_content, $relax_ng_path)) {
			$this->last_validation_error = $sedaValidation->getLastErrors();
			throw new Exception("Erreur lors de la validation du bordereau (validation du schéma RelaxNG)");
		}

		if (! $sedaValidation->validateSEDA($bordereau_content)){
			$this->last_validation_error = $sedaValidation->getLastErrors();
			throw new Exception("Erreur lors de la validation du bordereau (validation du schéma SEDA)");
		}
		return true;
	}

	public function getProprietePastellFlux(){
		$result = $this->getProprietePastell('flux');
		return array_merge($result,$this->getProprietePastell('file'));
	}

	public function getProprietePastellConnecteur(){
		return $this->getProprietePastell('connecteur');
	}

	public function getProprietePastell($type){
		$agape_file_path = $this->getAgapeFilePath();

		$agapeFile = new AgapeFile();

		$annotation_list = $agapeFile->getAllAnnotation($agape_file_path);
		$annotationWrapper = new AnnotationWrapper();
		$result = array();
		foreach($annotation_list as $annotation){
			$result= array_merge($result,$annotationWrapper->extractInfo($annotation));
		}
		$the_result = array();
		foreach($result as $command_info){
			list($command,$data) = $command_info;
			if ($command == $type){
				$the_result[] = $data;
			}
		}
		return $the_result;
	}

	public function generateArchive(FluxData $fluxData, $archive_path){
		$tmpFolder = new TmpFolder();
		$tmp_folder = $tmpFolder->create();

		$files_list = "";

		foreach($fluxData->getFilelist() as $file_id){

            $filename = $file_id['filename'];
            $filepath = $file_id['filepath'];

            if (! $filepath){
                break;
            }
            $dirname = dirname($tmp_folder."/".$filename);
            if (! file_exists($dirname)){
                mkdir($dirname,0777,true);
            }
            copy($filepath,"$tmp_folder/$filename");
            $files_list.= escapeshellarg($filename). " ";
        }


		$command = "tar cvzf $archive_path --directory $tmp_folder -- $files_list 2>&1";

		exec($command,$output,$return_var);

		if ( $return_var != 0) {
			$output = implode("\n",$output);
			throw new Exception("Impossible de créer le fichier d'archive $archive_path - status : $return_var - output: $output");
		}

		$tmpFolder->delete($tmp_folder);
	}

}