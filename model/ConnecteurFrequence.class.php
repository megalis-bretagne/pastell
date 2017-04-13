<?php


class ConnecteurFrequence {

	const TYPE_GLOBAL = 'global';
	const TYPE_ENTITE = 'entite';

	const TYPE_ACTION_DOCUMENT = 'document';
	const TYPE_ACTION_CONNECTEUR = 'connecteur';

	public $id_cf;

	public $type_connecteur;
	public $famille_connecteur;
	public $id_connecteur;
	public $id_ce;

	public $action_type;
	public $type_document;
	public $action;
	public $expression;
	public $id_verrou;

	public function __construct(array $input = array()) {
		foreach(get_object_vars($this) as $key => $value){
			if (isset($input[$key])) {
				$this->$key = $input[$key]?:'';
			} else {
				$this->$key = '';
			}
		}
		if (isset($input['id_cf'])) {
			$input['id_cf'] = intval($input['id_cf']);
		}
	}

	public function getArray(){
		return get_object_vars($this);
	}

	public function getAttributeName(){
		$result = $this->getArray();
		unset($result['id_cf']);
		return array_keys($result);
	}

	public function getConnecteurSelector(){
		if ($this->type_connecteur == ''){
			return 'Tous les connecteurs';
		}
		if ($this->type_connecteur == self::TYPE_GLOBAL){
			$result = "(Global)";
		} else {
			$result = "(Entité)";
		}

		if ($this->famille_connecteur == ''){
			return $result." Tous les connecteurs";
		}

		$result .= " ".$this->famille_connecteur;

		if ($this->id_connecteur == ''){
			return $result;
		}

		return $result.":".$this->id_connecteur;
	}

	public function getActionSelector(){
		if ($this->action_type == ''){
			return "Toutes les actions";
		}
		$result = "";
		if ($this->action_type == self::TYPE_ACTION_CONNECTEUR){
			$result .= "(Connecteur) ";
		} else {
			$result .= "(Document) ";
			if ($this->type_document == ''){
				return $result."Tous les types de documents";
			}
			$result .= "{$this->type_document}: ";
		}
		if ($this->action == ''){
			$result .= "toutes les actions";
		} else {
			$result .= $this->action;
		}
		return $result;
	}

	public function getNextTry($nb_try,$relative_date = ''){
		if (! $this->expression){
			return '';
		}

		$all_line = explode("\n",$this->expression);
		$frequence_list = array();
		foreach($all_line as $line){
			preg_match('#([^X]*)\s*X?\s*(\d*)#',$line,$matches);
			$frequence_list[] = array('expression'=>$matches[1],'nb_try'=>$matches[2]);
		}

		$total_try = 0;
		$i = 0;
		while ($total_try <= $nb_try && isset($frequence_list[$i]) && $frequence_list[$i]['nb_try']){
			$total_try += $frequence_list[$i]['nb_try'];
			if ($total_try<=$nb_try) {
				$i++;
			}
		}
		if (empty($frequence_list[$i])){
			throw new Exception("Trop d'essai sur le connecteur");
		}

		$frequence = $frequence_list[$i]['expression'];

		if (preg_match("#\(([^\)]*)\)#",$frequence,$matches)){
			$cron = Cron\CronExpression::factory($matches[1]);
			return $cron->getNextRunDate()->format("Y-m-d H:i:s");
		}

		$next_try_in_minutes = intval($frequence);
		return date('Y-m-d H:i:s', strtotime("$relative_date+ {$next_try_in_minutes} minutes"));
	}

}