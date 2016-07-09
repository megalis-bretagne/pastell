<?php
class AdminControler extends Controler {	

	/** @return UtilisateurCreator */
	private function getUtilisateurCreator(){
		return $this->getInstance('UtilisateurCreator');
	}

	/** @return RoleDroit */
	private function getRoleDroit(){
		return $this->getInstance('RoleDroit');
	}

	/** @return RoleSQL */
	private function getRoleSQL(){
		return $this->getInstance('RoleSQL');
	}

	/** @return Utilisateur */
	private function getUtilisateur(){
		return $this->getInstance('Utilisateur');
	}

	/** @return RoleUtilisateur */
	private function getRoleUtilisateur(){
		return $this->getInstance('RoleUtilisateur');
	}

	/** @return EntiteCreator */
	private function getEntiteCreator(){
		return $this->getInstance('EntiteCreator');
	}

	public function createAdmin($login,$password,$email){
		$this->fixDroit();
		$id_u = $this->getUtilisateurCreator()->create($login,$password,$password,$email);
		if (!$id_u){
			$this->lastError = $this->getUtilisateurCreator()->getLastError();
			return false; 
		}
		//Ajout de l'affectation du nom (reprise du login) pour avoir accès à la fiche de l'utilisateur depuis l'IHM
		$this->getUtilisateur()->setNomPrenom($id_u,$login,"");
		$this->getUtilisateur()->validMailAuto($id_u);
		$this->getUtilisateur()->setColBase($id_u,0);
		$this->getRoleUtilisateur()->addRole($id_u,"admin",0);
		return true;
	}
	
	public function fixDroit(){
		$this->getRoleSQL()->edit("admin","Administrateur");
		
		foreach($this->getRoleDroit()->getAllDroit() as $droit){
			$this->getRoleSQL()->addDroit("admin",$droit);
		}
		$this->getEntiteCreator()->updateAllEntiteAncetre();
	}
}

