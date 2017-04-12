<?php


class ConnecteurFrequenceSQL extends SQL {

	public function edit(ConnecteurFrequence $connecteurFrequence){
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
			$sql = "INSERT INTO connecteur_frequence($sql_part1) VALUES ";
			$sql .= "(".implode(",",array_fill(0,8,"?")).")";
			$this->query(
				$sql,
				array_values($attribute_list)
			);
			return $this->lastInsertId();
		}
	}

	public function getInfo($id_cf){
		$sql = "SELECT * FROM connecteur_frequence WHERE id_cf = ?";
		return $this->queryOne($sql,$id_cf);
	}

	/**
	 * @return ConnecteurFrequence[]
	 */
	public function getAll(){
		$sql = "SELECT * FROM connecteur_frequence";
		$result = $this->query($sql);
		foreach($result as $i => $line){
			$result[$i] = new ConnecteurFrequence($line);
		}
		return $result;
	}

	public function getConnecteurFrequence($id_cf){
		$info = $this->getInfo($id_cf);
		if (! $info['id_cf']){
			return null;
		}
		$connecteurFrequence = new ConnecteurFrequence();
		foreach($connecteurFrequence->getArray() as $key => $value){
			$connecteurFrequence->$key = $info[$key];
		}
		return $connecteurFrequence;
	}

	public function delete($id_cf){
		$sql = "DELETE FROM connecteur_frequence WHERE id_cf =?";
		$this->query($sql,$id_cf);
	}
}