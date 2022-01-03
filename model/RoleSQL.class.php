<?php

class RoleSQL extends SQL
{
    public function getInfo($role)
    {
        $sql = "SELECT * FROM role WHERE role=?";
        return $this->queryOne($sql, $role);
    }

    public function getAllRole()
    {
        $sql = "SELECT * FROM role ORDER by libelle";
        $result = $this->query($sql);
        return $result;
    }

    public function edit($role, $libelle)
    {
        $sql = "SELECT count(*) FROM role WHERE role=?";
        if ($this->queryOne($sql, $role)) {
            $sql = "UPDATE role SET libelle = ? WHERE role = ? ";
            $this->query($sql, $libelle, $role);
        } else {
            $sql = "INSERT INTO role (role,libelle) VALUES (?,?)";
            $this->query($sql, $role, $libelle);
        }
    }

    public function getDroit(array $allDroit, $role)
    {
        $result = array_fill_keys($allDroit, false);
        $sql = "SELECT * FROM role_droit WHERE role=? ORDER BY role_droit.droit";
        foreach ($this->query($sql, $role) as $line) {
            $result[$line['droit']] = true;
        }
        return $result;
    }

    public function updateDroit($role, array $lesDroits)
    {
        $sql = "DELETE FROM role_droit WHERE role=?";
        $this->query($sql, $role);
        foreach ($lesDroits as $droit) {
            $this->insertDroit($role, $droit);
        }
    }

    private function insertDroit($role, $droit)
    {
        $sql = "INSERT INTO role_droit(role,droit) VALUES (?,?)";
        $this->query($sql, $role, $droit);
    }

    public function addDroit($role, $droit)
    {
        $sql = "SELECT count(*) FROM role_droit WHERE role=? AND droit=?";
        if ($this->queryOne($sql, $role, $droit)) {
            return;
        }
        $this->insertDroit($role, $droit);
    }

    public function delete($role)
    {
        $sql = "DELETE FROM role_droit WHERE role=?";
        $this->query($sql, $role);
        $sql = "DELETE FROM role WHERE role=?";
        $this->query($sql, $role);
    }

    public function getAuthorizedRoleToDelegate(array $droit_list)
    {
        $sql = "SELECT role FROM role";
        $role_list = array_flip($this->queryOneCol($sql));
        $sql = "SELECT * FROM role_droit";
        foreach ($this->query($sql) as $role_droit) {
            if (! in_array($role_droit['droit'], $droit_list)) {
                unset($role_list[$role_droit['role']]);
            }
        }
        return array_values(array_filter(array_flip($role_list)));
    }

    public function getRoleLibelle(array $role_id)
    {
        $result = array();
        foreach ($this->getAllRole() as $role) {
            if (in_array($role['role'], $role_id)) {
                $result[] = array('role' => $role['role'],'libelle' => $role['libelle']);
            }
        };
        return $result;
    }

    public function getRoleByDroit($droit)
    {
        $sql = "SELECT role FROM role_droit WHERE droit=? ORDER BY role";
        return $this->queryOneCol($sql, $droit);
    }
}
