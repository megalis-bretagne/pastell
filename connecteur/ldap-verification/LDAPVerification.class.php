<?php 

class LDAPVerification extends Connecteur {
	
	private $ldap_host;
	private $ldap_port;
	private $ldap_user;
	private $ldap_password;
	private $ldap_filter;
	private $ldap_dn;
	private $ldap_root;
	private $ldap_login_attribute;

	function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
		foreach(array(	'ldap_host',
						'ldap_port',
						'ldap_user',
						'ldap_password',
						'ldap_filter',
						'ldap_dn',
						'ldap_root',
						'ldap_login_attribute'
				) as $variable){
			$this->$variable = $donneesFormulaire->get($variable);
		}
	}
	
	public function getConnexion(){
		$ldap = $this->getConnexionObject();
		if (! @ ldap_bind($ldap,$this->ldap_user,$this->ldap_password)){
			throw new Exception("Impossible de s'authentifier sur le serveur LDAP : ".ldap_error($ldap));
		}
		return $ldap;
	}

	private function getConnexionObject(){
		$ldap = ldap_connect($this->ldap_host,$this->ldap_port);
		if (!$ldap){
			throw new Exception("Impossible de se connecter sur le serveur LDAP : " . ldap_error($ldap));
		}
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
		return $ldap;
	}


	public function getLogin($dn){
		if (! $this->ldap_dn){
			return false;
		}
		$regexp = preg_replace("#%LOGIN%#","([^,]*)",$this->ldap_dn);
		preg_match("#$regexp#u",$dn,$matches);
		if(isset($matches[1])){
			return $matches[1];
		}
		return false;
	}
	
	public function getEntry($user_id){
		$ldap = $this->getConnexion();
		//$dn = $this->getUserDN($user_id);
		$filter = $this->ldap_filter;
		if (!$filter){
			$filter = "(objectClass=*)";
		}
		$filter = "(&$filter({$this->ldap_login_attribute}=$user_id))";

		$result = ldap_search($ldap,$this->ldap_root,$filter);
		if (! $result || ldap_count_entries($ldap,$result) < 1){
			return array();
		}
		$entries = ldap_get_entries($ldap,$result);
		if (empty($entries[0]['dn'])){
			return false;
		}
		return $entries[0]['dn'];
	}

	private function getUserDN($user_id){
		if ($this->ldap_dn) {
			return preg_replace("#%LOGIN%#", $user_id, $this->ldap_dn);
		}

		return $this->getEntry($user_id);
	}

	public function getAllUser(){
		$ldap = $this->getConnexion();
		$dn = $this->ldap_root;
		$filter = $this->ldap_filter;
		if (!$filter){
			$filter = "(objectClass=*)";
		}
		$result = @ ldap_search($ldap,$dn,$filter,array($this->ldap_login_attribute,'sn','mail','givenname'));
		if (! $result || ldap_count_entries($ldap,$result) < 1){
			return array();
		}
		$entries = ldap_get_entries($ldap,$result);
		return $entries;
	}

	private function getAttribute($entry,$attribute_name){
		if (empty($entry[$attribute_name][0])){
			return "";
		}
		return utf8_decode($entry[$attribute_name][0]);
	}

	public function getUserToCreate(Utilisateur $utilisateur){
		$entries = $this->getAllUser();
		unset($entries['count']);
		$result = array();
		foreach($entries as $entry){
			$login = $this->getLogin($entry['dn']);
			if (! $login) {
				$login = $this->getAttribute($entry,$this->ldap_login_attribute);
			}
			if (!$login){
				continue;
			}
			$email = $this->getAttribute($entry,'mail');
			$prenom = $this->getAttribute($entry,'givenname');
			$nom = $this->getAttribute($entry,'sn');
			
			$ldap_info = array('login'=>$login,'prenom'=>$prenom,'nom'=>$nom,'email'=>$email);
			$id_u = $utilisateur->getIdFromLogin($login); 
			if (! $id_u){
				$ldap_info['create'] = true;
				$ldap_info['synchronize'] = true;
			} else {
				$ldap_info['create'] = false;
				$info = $utilisateur->getInfo($id_u);
				$ldap_info['id_u'] = $info['id_u'];
				$ldap_info['synchronize'] = $info['prenom'] != $prenom || $info['nom'] != $nom || $info['email'] != $email;
			}
			$result[] = $ldap_info;
		}
		return $result;
	}

	public function verifLogin($login,$password){
		if (! $login){
			return false;
		}
		$ldap = $this->getConnexionObject();
		$user_id = $this->getUserDN($login);
		if (! @ ldap_bind($ldap,$user_id,$password)){
			return false;
		}
		return true;
	}

}