<?php

class FluxEntiteSQL extends SQL
{
    
    public const FLUX_GLOBAL_NAME = 'global';
    
    private function getFluxName($id_e, $flux)
    {
        if ($id_e === 0) {
            $flux = self::FLUX_GLOBAL_NAME;
        }
        return $flux;
    }

    public function getConnecteur($id_e, $flux, $connecteur_type, $num_same_type = 0)
    {
        $flux = $this->getFluxName($id_e, $flux);
        $sql = "SELECT flux_entite.*,connecteur_entite.*,entite.denomination FROM flux_entite " .
                " JOIN connecteur_entite ON flux_entite.id_ce=connecteur_entite.id_ce " .
                " LEFT JOIN entite ON connecteur_entite.id_e=entite.id_e " .
                " WHERE flux_entite.id_e=? AND flux=? AND flux_entite.type=? AND flux_entite.num_same_type=?";
        
        return $this->queryOne($sql, $id_e, $flux, $connecteur_type, $num_same_type);
    }
    
    public function getConnecteurId($id_e, $flux, $connecteur_type, $num_same_type = 0)
    {
        $flux = $this->getFluxName($id_e, $flux);
        $sql = "SELECT flux_entite.id_ce FROM flux_entite " .
                " JOIN connecteur_entite ON flux_entite.id_ce=connecteur_entite.id_ce " .
                " WHERE flux_entite.id_e=? AND flux=? AND flux_entite.type=? AND flux_entite.num_same_type = ?";
        return $this->queryOne($sql, $id_e, $flux, $connecteur_type, $num_same_type);
    }
    
    public function getConnecteurById($id_fe)
    {
        $sql = "SELECT * FROM flux_entite WHERE id_fe=?";
        return $this->queryOne($sql, $id_fe);
    }

    public function getAllWithSameType($id_e)
    {
        $sql = "SELECT flux_entite.*,connecteur_entite.*,entite.denomination FROM flux_entite" .
            " JOIN connecteur_entite ON flux_entite.id_ce=connecteur_entite.id_ce " .
            " LEFT JOIN entite ON connecteur_entite.id_e=entite.id_e " .
            " WHERE flux_entite.id_e=?";
        $result = array();
        foreach ($this->query($sql, $id_e) as $line) {
            $result[$line['flux']][$line['type']][$line['num_same_type']] = $line;
        }
        return $result;
    }

    /**
     * @deprecated 3.0 use getAllWithSameType() instead
     * @param $id_e
     * @return array
     */
    public function getAll($id_e)
    {
        $sql = "SELECT flux_entite.*,connecteur_entite.*,entite.denomination FROM flux_entite" .
                " JOIN connecteur_entite ON flux_entite.id_ce=connecteur_entite.id_ce " .
                " LEFT JOIN entite ON connecteur_entite.id_e=entite.id_e " .
                " WHERE flux_entite.id_e=?";
        $result = array();
        foreach ($this->query($sql, $id_e) as $line) {
            $result[$line['flux']][$line['type']] = $line;
        }
        return $result;
    }

    public function getAllFluxEntite($id_e, $flux = null, $type = null)
    {
        $sql = "SELECT * FROM flux_entite WHERE id_e=? ";
        $data = array($id_e);
        if ($flux) {
            $sql .= " AND flux=? ";
            $data[] = $flux;
        }
        if ($type) {
            $sql .= " AND type=? ";
            $data[] = $type;
        }
        $sql .= " ORDER BY id_fe ";
        return $this->query($sql, $data);
    }
        
    public function addConnecteur($id_e, $flux, $type, $id_ce, $num_same_type = 0)
    {
        $flux = $this->getFluxName($id_e, $flux);
        $this->deleteConnecteur($id_e, $flux, $type, $num_same_type);
        $sql = "INSERT INTO flux_entite(id_e,flux,type,id_ce,num_same_type) VALUES (?,?,?,?,?)";
        $this->query($sql, $id_e, $flux, $type, $id_ce, $num_same_type);
        return $this->lastInsertId();
    }
    
    public function deleteConnecteur($id_e, $flux, $type, $num_same_type = 0)
    {
        $flux = $this->getFluxName($id_e, $flux);
        $sql = "DELETE FROM flux_entite " .
                " WHERE id_e=? AND type=? AND flux=? AND num_same_type=?";
        $this->query($sql, $id_e, $type, $flux, $num_same_type);
    }
    
    public function removeConnecteur($id_fe)
    {
        $sql = "DELETE FROM flux_entite WHERE id_fe=?";
        $this->query($sql, $id_fe);
    }

    public function getFluxByConnecteur($id_ce)
    {
        $sql = "SELECT flux FROM flux_entite" .
            " JOIN connecteur_entite ON flux_entite.id_ce=connecteur_entite.id_ce " .
            " WHERE connecteur_entite.id_ce=?";
        return $this->queryOneCol($sql, $id_ce);
    }

    public function getUsedByConnecteurIfUnique($id_ce, $id_e)
    {
        $all_used = $this->getUsedByConnecteur($id_ce, null, $id_e);

        if (count($all_used) == 1) {
            return $all_used[0]['flux'];
        }
        return false;
    }

    public function getUsedByConnecteur($id_ce, $flux = null, $id_e = null)
    {
        $sql = "SELECT * FROM flux_entite WHERE id_ce=? ";
        $data = array($id_ce);
        if ($flux) {
            $sql .= " AND flux=? ";
            $data[] = $flux;
        }
        if ($id_e) {
            $sql .= " AND id_e=? ";
            $data[] = $id_e;
        }
        return $this->query($sql, $data);
    }

    /**
     * @deprecated use getFluxByConnecteur() instead
     * @param $id_ce
     * @return array
     */
    public function isUsed($id_ce)
    {
        return $this->getFluxByConnecteur($id_ce);
    }

    public function getEntiteByFlux($flux)
    {
        $sql = "SELECT DISTINCT entite.id_e,entite.denomination FROM flux_entite " .
            " JOIN entite ON flux_entite.id_e=entite.id_e WHERE flux=?";
        return $this->query($sql, $flux);
    }
}
