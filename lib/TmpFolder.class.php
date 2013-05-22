<?php 

class TmpFolder {
	
	public function create(){
	 	$folder_name = sys_get_temp_dir() . "/pastell_tmp_folder_" . mt_rand(0,mt_getrandmax());
    	if (file_exists($folder_name)) {
    		throw new Exception("Impossible de cr�er un r�petoire temporaire : le r�pertoire existe"); 
    	}
    	mkdir($folder_name) ;
    	if (! is_dir($folder_name)) { 
    		 throw new Exception("Acc�s impossible au r�petoire temporaire");
    	}
    	return $folder_name;
	}
	
	public function delete($folder_name){
		if (! is_dir($folder_name)) {
				return;
		}
		foreach ( scandir($folder_name) as $object) {
			if (in_array($object,array(".",".."))) {
				continue;
			}
			if (is_dir("$folder_name/$object")){
				$this->delete("$folder_name/$object");
			} else {
				unlink("$folder_name/$object");
			}
		}
		rmdir($folder_name);
	}
	
	
}