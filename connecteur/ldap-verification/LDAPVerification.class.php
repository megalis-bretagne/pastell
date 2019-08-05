<?php 

class LDAPVerification extends Connecteur {
	
	private $ldap_host;
	private $ldap_port;
	private $ldap_user;
	private $ldap_password;
	private $ldap_filter;
	private $ldap_root;
	private $ldap_login_attribute;

	function setConnecteurConfig(DonneesFormulaire $donneesFormulaire){
		foreach(array(	'ldap_host',
						'ldap_port',
						'ldap_user',
						'ldap_password',
						'ldap_filter',
						'ldap_root',
						'ldap_login_attribute'
				) as $variable){
			$this->$variable = $donneesFormulaire->get($variable);
		}
	}

    /**
     * @return resource
     * @throws Exception
     */
    public function getConnexion(){
		$ldap = $this->getConnexionObject();
		if (! @ ldap_bind($ldap,$this->ldap_user,$this->ldap_password)){
			throw new Exception("Impossible de s'authentifier sur le serveur LDAP : ".ldap_error($ldap));
		}
		return $ldap;
	}

    /**
     * @return resource
     * @throws Exception
     */
    private function getConnexionObject(){
		$ldap = ldap_connect($this->ldap_host,$this->ldap_port);
		if (!$ldap){
			throw new Exception("Impossible de se connecter sur le serveur LDAP : " . ldap_error($ldap));
		}
		ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
		return $ldap;
	}

    /**
     * @param $user_id
     * @return array|bool
     * @throws Exception
     */
    public function getEntry($user_id){
		$ldap = $this->getConnexion();
		$filter = $this->ldap_filter;
		if (!$filter){
			$filter = "(objectClass=*)";
		}
		$filter = "(&($filter)({$this->ldap_login_attribute}=$user_id))";

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

    /**
     * @param $user_id
     * @return array|bool
     * @throws Exception
     */
    private function getUserDN($user_id){
		return $this->getEntry($user_id);
	}

    /**
     * @return array
     * @throws Exception
     */
    public function getAllUser(){
		$ldap = $this->getConnexion();
		$dn = $this->ldap_root;
		$filter = $this->ldap_filter;
		if (!$filter){
			$filter = "(objectClass=*)";
		}
		$result = @ ldap_search($ldap,$dn,$filter,array($this->ldap_login_attribute,'sn','mail','givenname'));

		if ( $result === false ){
			$error = ldap_error($ldap);
			if ($error){
				throw new Exception($error);
			}
			return array();
		}
		if (ldap_count_entries($ldap,$result)<1){
			throw new Exception("Aucun utilisateur n'a été retourné");
		}

		return ldap_get_entries($ldap,$result);
	}

	private function getAttribute($entry,$attribute_name){
		if (empty($entry[$attribute_name][0])){
			return "";
		}
		return $entry[$attribute_name][0];
	}

    /**
     * @param Utilisateur $utilisateur
     * @return array
     * @throws Exception
     */
    public function getUserToCreate(Utilisateur $utilisateur){
		$entries = $this->getAllUser();
		unset($entries['count']);
		$result = array();
		foreach($entries as $entry){
		    $login = $this->getAttribute($entry,$this->ldap_login_attribute);
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

    /**
     * @param $login
     * @param $password
     * @return bool
     * @throws Exception
     */
    public function verifLogin($login, $password){
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