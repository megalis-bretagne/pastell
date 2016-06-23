<?php
class JSONoutput {
	
	public function displayErrorAndExit($Errormessage){
		$result['status'] = 'error';
		$result['error-message'] = $Errormessage;;
		$this->display($result);
		exit_wrapper();
	} // @codeCoverageIgnore
	
	public function display(array $array){
		header_wrapper("Content-type: text/plain");
		echo $this->getJson($array);
	}	

	private function getJson(array $array){
		return json_encode($array);
	}

}