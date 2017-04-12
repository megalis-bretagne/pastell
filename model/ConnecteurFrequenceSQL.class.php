<?php


class ConnecteurFrequenceSQL extends SQL {

	public function create(ConnecteurFrequence $connecteurFrequence){
		if ($connecteurFrequence->id_cf){
			$attribute_list = $connecteurFrequence->getArray();
			unset($attribute_list['id_cf']);
			$sql_part = implode("=?,",array_keys($attribute_list))."=?";

			$attribute_list['id_cf'] = $connecteurFrequence->id_cf;

			$sql = "UPDATE connecteur_frequence SET $sql_part WHERE id_cf=?";
			$this->query($sql,
				array_values($attribute_list)
			);
			return $connecteurFrequence->id_cf;
		} else {
			$attribute_list = $connecteurFrequence->getArray();
			unset($attribute_list['id_cf']);
			$sql_part1 = implode(",",array_keys($attribute_list));

			$sql = "INSERT INTO connecteur_frequence($sql_part1)VALUES ".implode(",",array_fill(0,8,"?"));
			$this->query(
				$sql,
				array_values($attribute_list)
			);
			return $this->lastInsertId();
		}
	}

	public function getInfo($id_cf){
		$sql = "SELECT * FROM connecteur_frequence WHERE id_cf = ?";
		return $this->formatOutput($this->queryOne($sql,$id_cf));
	}

	public function getAll(){
		$sql = "SELECT * FROM connecteur_frequence";
		$result = $this->query($sql);
		foreach($result as $i => $line){
			$result[$i] = $this->formatOutput($line);
		}

		/*usort($result,function($a,$b) {

		});*/

		return $result;

	}

	public function delete($id_cf){
		$sql = "DELETE FROM connecteur_frequence WHERE id_cf =?";
		$this->query($sql,$id_cf);
	}

	public function formatOutput($line){

		$connecteurFrequence = new ConnecteurFrequence();

		;

		foreach(array_keys($connecteurFrequence->getArray()) as $key){
			if (! isset($line[$key])){
				$line[$key] = '';
			}
		}

		$line['connecteur_selector'] = $this->getConnecteurSelector($line);
		$line['action_selector'] = $this->getActionSelector($line);
		return $line;
	}

	public function getActionSelector($line){
		if ($line['action_type'] == ''){
			return "Toutes les actions";
		}
		$result = "";
		if ($line['action_type'] == 'connecteur'){
			$result .= "(Connecteur) ";
		} else {
			$result .= "(Document) ";
			if ($line['type_document'] == ''){
				return $result." Tous les types de documents";
			}
			$result .= "{$line['type_document']},";
		}
		if ($line['action'] == ''){
			$result .= "toutes les actions";
		} else {
			$result .= $line['action'];
		}

		return $result;
	}

	public function getConnecteurSelector($line){
		if ($line['type_connecteur'] == ''){
			return 'Tous les connecteurs';
		}
		$result = $line['type_connecteur'] == 'global' ? "(Global)":"(Entité)";

		if ($line['famille_connecteur'] == ''){
			return $result." Tous les connecteurs";
		}

		$result .= " ".$line['famille_connecteur'];

		if ($line['id_connecteur'] == ''){
			return $result;
		}

		return $result.":".$line['id_connecteur'];
	}




}