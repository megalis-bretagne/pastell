<?php 

class LibersignTestSHA1 extends ActionExecutor{
	
	public function go(){
		
		$my = $this->getMyConnecteur();
		$content = file_get_contents(__DIR__."/../fixtures/pes.xml");
		$sha1 = $my->getSha1($content);
		
		if ($sha1 != '272a08182071c3524160a9fda406ab4aeeffeb6c'){
			$this->setLastMessage("Empreinte : $sha1 != 272a08182071c3524160a9fda406ab4aeeffeb6c : FAIL !");
			return false;
		}
		$this->setLastMessage("Empreinte : $sha1 : OK");
		return true;
		
	}
	
}