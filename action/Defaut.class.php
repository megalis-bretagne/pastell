<?php
class Defaut extends ActionExecutor {

	public function go(){		
		$actionName  = $this->getActionName();
		$this->getActionCreator()->addAction($this->id_e,$this->id_u,$this->action,"L'action $actionName a �t� execut� sur le document");
		$this->setLastMessage("L'action $actionName a �t� execut� sur le document");
		return true;
	}

}