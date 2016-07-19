<?php

class ExtensionControler extends PastellControler {

	const WEB_PAGE_NAME = 'Extension/web/';

	public function webAction(){

		$page_request = $this->getGetInfo()->get(FrontController::PAGE_REQUEST);

		$offset = stripos($page_request, self::WEB_PAGE_NAME);
		if ( $offset === false ){
			throw new Exception(self::WEB_PAGE_NAME . " not found in ".FrontController::PAGE_REQUEST);
		}
		$true_page = substr($page_request, $offset + strlen(self::WEB_PAGE_NAME));

		$extension_name = strstr($true_page,"/",true);

		if($extension_name === false){
			throw new Exception("Unable to find extension name in page request");
		}

		/** @var Extensions $extensions */
		$extensions = $this->getInstance("Extensions");
		$extension_info = $extensions->getById($extension_name);

		if (! $extension_info){
			throw new Exception("Unable to find extension $extension_name");
		}

		$path_extension = $extension_info['path'];

		$web_path = $path_extension."/web/";

		if (! file_exists($web_path)){
			throw new Exception("Extension $extension_name has no /web/ directory");
		}


		$link_name = __DIR__."/../web/Extension/web/".$extension_name;
		if (file_exists($link_name)){
			throw new PastellNotFoundException("La page n'existe pas ou n'est pas utilisable");
		}

		if (! symlink($web_path,$link_name)){
			throw new Exception("Unable to set link : $link_name -> $web_path");
		}
		$this->absoluteRedirect($this->getServerInfo('REQUEST_URI'));
	}


}