<?php

class DatabaseEventMySQL {
	
	private $sqlCommand = array();
	
	public function getSQLCommand(){
		return $this->sqlCommand;
	}
	
	public function onCreateTable($tableName,array $tableDefinition){
		$this->sqlCommand[] =  "CREATE TABLE `$tableName` (\n\t".
				$this->linearizeTableDefinition($tableDefinition).
				"\n)  ENGINE={$tableDefinition['Engine']}  ;";
	}

	private function getColumnDefinition($name, array $columnDefinition,$withPrimaryKey = false){
		$r = "`$name` ".$columnDefinition['Type'];
		if ($columnDefinition['Null'] == 'NO'){
			$r .= " NOT NULL";
		}
		if ($columnDefinition['Default'] !== null){
			if ($columnDefinition['Type'] == 'timestamp' ){
				$r .= " DEFAULT {$columnDefinition['Default']}";
			} else {
				$r .= " DEFAULT '{$columnDefinition['Default']}'";
			}
		}
		if ($columnDefinition['Extra'] == 'auto_increment'){
			$r .= " AUTO_INCREMENT";
			if ($withPrimaryKey){
				$r .= " PRIMARY KEY ";
			}
		}	
		return $r;
	}
	
	private function linearizeTableDefinition(array $tableDefinition){
		$result = array();
		foreach($tableDefinition['Column'] as $name => $column){
			$result[] = $this->getColumnDefinition($name,$column);
		}
		
		foreach ($tableDefinition['Index'] as $name => $index){
			$col = $this->getLinerizedColumnForIndex($index['col']);
			if ($name == "PRIMARY"){
				$r = "PRIMARY KEY $col";
			} else {
				$r = "KEY `$name` $col";
				if ($index['unique']){
					$r = "UNIQUE $r ";
				}
				if ($index['type'] != 'BTREE'){
					$r = $index['type'] ." $r";
				}
				
			}
			$result[] = $r;
		}
		return implode(",\n\t",$result);
	}
	
	private function getLinerizedColumnForIndex(array $col){
		return "(`".implode("`,`",$col)."`)";
	}
	
	
	public function onDropTable($tableName) { 
		$this->sqlCommand[] = "DROP TABLE `$tableName`;";
	}
	
	public function onChangeEngine($tableName,$engine){
		$this->sqlCommand[] = "ALTER TABLE `$tableName` ENGINE = $engine;";
	}
	
	public function onAddColumn($tableName,$columnName,array $columnDefinition) {
		$this->sqlCommand[] = "ALTER TABLE `$tableName` ADD ".$this->getColumnDefinition($columnName,$columnDefinition,true) .";";
	}
	
	public function onDropColumn($tableName,$columnName) {
		$this->sqlCommand[] = "ALTER TABLE `$tableName` DROP `$columnName`;";
	}
	
	public function onChangeColumn($tableName,$columnName,array $definition1,array $definition2) {
		$this->sqlCommand[] = "ALTER TABLE `$tableName` CHANGE `$columnName` " . $this->getColumnDefinition($columnName,$definition1).";"; 
	}
	
	public function onAddIndex($tableName, array $indexDefinition) {
		$col = $this->getLinerizedColumnForIndex($indexDefinition['col']); 
		
		$begin = "";
		if ($indexDefinition['type'] == 'FULLTEXT'){
			$begin = ' FULLTEXT';
		}
		if ($indexDefinition['unique']){
			$begin = ' UNIQUE';
		}

		if ($indexDefinition['name'] == 'PRIMARY'){
			$index_name = "PRIMARY";
		} else {
			$index_name = "`{$indexDefinition['name']}`";
		}

		$this->sqlCommand[] = "CREATE $begin INDEX $index_name ON `$tableName` $col ;";
	}
	
	public function onDropIndex($tableName,$indexName) {
		$this->sqlCommand[] = "DROP INDEX `$indexName` ON `$tableName`;";
	}
	
	public function onChangeIndexName($tableName,$oldName,$newName,array $indexDefinition) {
		$this->onDropIndex($tableName,$oldName);
		$this->onAddIndex($tableName,$indexDefinition);
	}

}