<?php


class ConnecteurFrequenceSQL extends SQL {

	public function create(ConnecteurFrequence $connecteurFrequence){
		$sql = "INSERT INTO connecteur_frequence(type_connecteur,famille_connecteur,id_connecteur) VALUES (?,?,?)";
		$this->query(
			$sql,
			$connecteurFrequence->type_connecteur,
			$connecteurFrequence->famille_connecteur,
			$connecteurFrequence->id_connecteur
		);
		return $this->lastInsertId();
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
		return $result;

	}

	private function formatOutput($line){
		$line['connecteur_selector'] = $line['type_connecteur']."-".$line['famille_connecteur']."-".$line['id_connecteur'];
		$line['action_selector'] = "";
		return $line;
	}


}