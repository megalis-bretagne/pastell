<?php

class DocumentEntite extends SQL {
	public function getDocument($id_e,$type){
		$sql = "SELECT * FROM document_entite " .  
				" JOIN document ON document_entite.id_d = document.id_d" .
				" WHERE document_entite.id_e = ? AND document.type=? " . 
				" ORDER BY document.modification DESC";	
		return $this->query($sql,$id_e,$type);
	}
	
	public function addRole($id_d,$id_e,$role){
		if ($this->hasRole($id_d,$id_e,$role)){
			return;
		}
		$type = $this->queryOne("SELECT type FROM document WHERE id_d=?",$id_d);

		$sql = "INSERT INTO document_entite (id_d,id_e,role,last_type) VALUES (?,?,?,?)";
		$this->query($sql,$id_d,$id_e,$role,$type);
	}
	
	public function hasRole($id_d,$id_e,$role){
		$sql = "SELECT count(*) FROM document_entite WHERE id_d=? AND id_e=? AND role= ?";
		return $this->queryOne($sql,$id_d,$id_e,$role);
	}
	
	public function getEntite($id_d){
		$sql = "SELECT * FROM document_entite JOIN entite ON document_entite.id_e = entite.id_e WHERE id_d=?";
		return $this->query($sql,$id_d);
	}
	
	public function getRole($id_e,$id_d){
		$sql = "SELECT role FROM document_entite WHERE id_e=? AND id_d=? LIMIT 1";
		return  $this->queryOne($sql,$id_e,$id_d);
	}
	
	public function getEntiteWithRole($id_d,$role){
		$sql = "SELECT id_e FROM document_entite WHERE id_d=? AND role=? LIMIT 1";
		return  $this->queryOne($sql,$id_d,$role);
	}
	
	public function getFromAction($type,$action){
		$sql = "SELECT * FROM document_entite " . 
				" JOIN document ON document_entite.id_d = document.id_d " . 
				" WHERE type = ? AND document_entite.last_action=?"; 
		return $this->query($sql,$type,$action);
	}
	
	public function getAll($id_e){
		$sql = "SELECT * FROM document_entite " .
				" JOIN document ON document_entite.id_d=document.id_d " .
				" WHERE id_e=?";
		return $this->query($sql,$id_e);
	}
	
	public function getNbAll($id_e){
		$sql = "SELECT count(*) FROM document_entite " .
				" JOIN document ON document_entite.id_d=document.id_d " .
				" WHERE id_e=?";
		return $this->queryOne($sql,$id_e);
	}

	public function getAllByFluxAction($flux,$action_from){
		$sql = "SELECT * FROM document_entite " .
			" JOIN document ON document_entite.id_d=document.id_d " .
			" JOIN entite ON document_entite.id_e = entite.id_e " .
			" WHERE document.type=? AND last_action=?";
		return $this->query($sql,$flux,$action_from);
	}


	public function getAllLocal() {
		$sql = "SELECT * FROM document_entite " .
			" JOIN document ON document_entite.id_d=document.id_d " .
			" WHERE id_e != 0";
		return $this->query($sql);
	}
	
	public function fixAction($flux,$action_from,$action_to){
		$sql = "UPDATE document_entite " .
				" JOIN document ON document_entite.id_d=document.id_d " .
				" SET last_action=?" . 
				" WHERE document.type=? AND last_action=?";
		$this->query($sql,$action_to,$flux,$action_from);
		
		$sql = "UPDATE document_action " .
				" JOIN document ON document_action.id_d=document.id_d " .
				" SET action=?" . 
				" WHERE document.type=? AND action=?";
		$this->query($sql,$action_to,$flux,$action_from);
	}

	public function getCountAction($id_e,$type){
		$sql = "SELECT count(*) as count,id_e,last_type as type,last_action FROM document_entite ";

		$data = [];
		$where_clause = [];
		if ($id_e){
			$where_clause[]= " id_e = ?";
			$data[] = $id_e;
		}
		if ($type){
			$where_clause[] = " last_type = ? ";
			$data[] = $type;
		}
		if ($where_clause) {
			$sql .= " WHERE ". implode(" AND ", $where_clause);
		}
		$sql .= " GROUP BY id_e,last_type,last_action;";
		return $this->query($sql,$data);
	}


	public function fixLastType(){
		$sql = "UPDATE document_entite JOIN document ON document_entite.id_d=document.id_d SET document_entite.last_type=document.type";
		$this->query($sql);
	}

    private function buildFiltersClause($id_e, $type, $filters)
    {
        $result = array();

        if (empty($filters)) {
            $val = sprintf("SELECT id_d FROM document_index JOIN document_entite USING(id_d) "
                . "JOIN document USING(id_d) WHERE id_e=%d AND type='%s'",
                $id_e, $type);
            $result[] = $val;
        } else {
            foreach ($filters as $k => $v) {
                $val = sprintf("SELECT id_d FROM document_index JOIN document_entite USING(id_d) "
                    . "JOIN document USING(id_d) WHERE id_e=%d AND type='%s' AND field_name='%s' AND field_value LIKE '%%%s%%'",
                    $id_e, $type, $k, $v);
                $result[] = $val;
            }
        }
        return $result;
    }

    private function buildRequestOneClause($clause)
    {
        $req = sprintf("SELECT COUNT(*) AS cnt FROM (SELECT DISTINCT id_d FROM (%s)T)C", $clause);
        return $req;
    }

    private function buildRequestMultiClauses($clauses)
    {
        $first = $clauses[0];
        $req = sprintf("SELECT COUNT(*) AS cnt FROM (SELECT DISTINCT id_d FROM (%s)T WHERE id_d IN ", $first);

        unset($clauses[0]);

        foreach ($clauses as $clause) {
            $v = sprintf("(%s AND id_d IN ", $clause);
            $req .= $v;
        }

        // suppression du ' AND id_d IN ' final
        $l = strlen(' AND id_d IN ');
        $req = substr($req, 0, -$l);
        $v = str_pad('', count($clauses), ')');
        $req .= $v . ')C';
        return $req;
    }

    public function getCountByEntityFormat($id_e, $type, $req)
    {
        $clauses = $this->buildFiltersClause($id_e, $type, $req);

        if (count($clauses) == 1) {
            $sql = $this->buildRequestOneClause($clauses[0]);
        } else {
            $sql = $this->buildRequestMultiClauses($clauses);
        }

        $v = $this->query($sql);
        $res['count'] = (int)$v[0]['cnt'];
        // pour le debug : à mettre dans les traces
        $res['SQL'] = $sql;
        return $res;
    }
}