<?php
class Defaut extends ActionExecutor {

	public function go(){		
		$actionName  = $this->getActionName();
		$this->addActionOK("L'action $actionName a �t� execut� sur le document");
		return true;
	}

}