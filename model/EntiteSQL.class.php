<?php

class EntiteSQL extends SQL
{
    
    public const ENTITE_RACINE_DENOMINATION = "Entité racine";

    public const ID_E_ENTITE_RACINE = 0;
    
    public function getInfo($id_e)
    {
        $sql = "SELECT * FROM entite WHERE id_e=?";
        return $this->queryOne($sql, $id_e);
    }
    
    public function exists($id_e)
    {
        return (bool) $this->getInfo($id_e);
    }
    
    public function getBySiren($siren)
    {
        $sql = "SELECT id_e FROM entite WHERE siren=?";
        return $this->queryOne($sql, $siren);
    }

    public function getIdByDenomination($denomination)
    {
        $sql = "SELECT id_e FROM entite WHERE denomination=?";
        return $this->queryOne($sql, $denomination);
    }
    
    public function getDenomination($id_e)
    {
        if ($id_e == 0) {
            return self::ENTITE_RACINE_DENOMINATION;
        }
        $info = $this->getInfo($id_e);
        if (! $info) {
            return "";
        }
        return $info['denomination'];
    }
    
    public function getEntiteMere($id_e)
    {
        $sql = "SELECT entite_mere FROM entite WHERE id_e=?";
        return $this->queryOne($sql, $id_e);
    }
    
    public function getAncetre($id_e)
    {
        $sql = "SELECT * FROM entite_ancetre " .
                " JOIN entite ON entite_ancetre.id_e_ancetre=entite.id_e " .
                " WHERE entite_ancetre.id_e=? ORDER BY niveau DESC";
        return $this->query($sql, $id_e);
    }

    /**
     * @param $id_e
     * @return bool
     */
    public function getCollectiviteAncetre($id_e)
    {
        $info = $this->getInfo($id_e);
        
        if (
                $info['type'] == Entite::TYPE_COLLECTIVITE ||
                $info['type'] == Entite::TYPE_CENTRE_DE_GESTION
        ) {
            return $id_e;
        }
        foreach ($this->getAncetre($id_e) as $ancetre) {
            if ($ancetre['type'] == Entite::TYPE_COLLECTIVITE) {
                return $ancetre['id_e'];
            }
        }
        return false;
    }
    
    public function getCDG($id_e)
    {
        return $this->getHeritedInfo($id_e, 'centre_de_gestion');
    }
    
    private function getHeritedInfo($id_e, $colname)
    {
        $info = $this->getInfo($id_e);
        if ($info[$colname]) {
            return $info[$colname];
        }
        
        $ancetre = $this->getAncetre($id_e);
        foreach ($ancetre as $id => $info) {
            if ($info[$colname]) {
                return $info[$colname];
            }
        }
        return false;
    }

    public function getExtendedInfo($id_e)
    {
        $result = $this->getInfo($id_e);
        $cdg_id_e = $this->getCDG($id_e);
        $result['cdg'] = array();
        if ($cdg_id_e) {
            $result['cdg'] = $this->getInfo($cdg_id_e) ;
        }
        if ($result['entite_mere']) {
            $result['entite_mere'] = $this->getInfo($result['entite_mere']) ;
        }
        $result['filles'] = $this->getFille($id_e);
        return $result;
    }
    
    public function getFille($id_e)
    {
        $sql = "SELECT * FROM entite WHERE entite_mere=? ORDER BY denomination";
        return $this->query($sql, $id_e);
    }
    
    public function getFilleInfoNavigation($id_e, array $liste_collectivite = array())
    {
        if ($id_e != 0 || ! $liste_collectivite || ($liste_collectivite[0] == 0)) {
            return $this->getNavigationFilleWithType($id_e, array(Entite::TYPE_COLLECTIVITE,Entite::TYPE_CENTRE_DE_GESTION,Entite::TYPE_SERVICE));
        }
        $liste_fille = array();

        foreach ($liste_collectivite as $id_e_fille) {
            $info_fille = $this->getInfo($id_e_fille);
            if ($info_fille['is_active']) {
                $liste_fille[] = $info_fille;
            }
        }
        return $liste_fille;
    }
    

    public function getSiren($id_e)
    {
        return $this->getHeritedInfo($id_e, 'siren');
    }
    
    public function getAll()
    {
        $sql = "SELECT * FROM entite";
        return $this->query($sql);
    }
    
    
    public function getAncetreId($id_e)
    {
        $ancetre = $this->getAncetre($id_e);
        array_pop($ancetre);
        $result = array(0);
        foreach ($ancetre as $entite) {
            $result[] = $entite['id_e'];
        }
        return $result;
    }
    
    private function getNavigationFilleWithType($id_e, array $type)
    {
        foreach ($type as $i => $t) {
            $type[$i] = "'$t'";
        }
        $sql = "SELECT * FROM entite " .
                " WHERE entite_mere=? " .
                " AND type IN (" . implode(",", $type) . ")" .
                " AND is_active = 1 " .
                " ORDER BY denomination";

        $result  = $this->query($sql, $id_e);
        //print_r($result);
        return $result;
    }
    
    public function getAncetreNav($id_e, $listeCollectivite)
    {
        $all_ancetre = $this->getAncetre($id_e);
        
        array_pop($all_ancetre);
        
        if (in_array(0, $listeCollectivite)) {
            return $all_ancetre;
        }
        
        $allParent = array();
        foreach ($all_ancetre as $parent) {
            $allParent[] = $parent['id_e'];
        }
        foreach ($allParent as $parent_id_e) {
            if (! in_array($parent_id_e, $listeCollectivite)) {
                array_shift($all_ancetre);
            } else {
                return $all_ancetre;
            }
        }
        return $all_ancetre;
    }
        
        // ajout de la methode pour la suppression des entites par API.
        // Les controles avant suppression sont à completer dans la methode appelante.
    /**
     * @param $id_e
     * @throws UnrecoverableException
     */
    public function removeEntite($id_e)
    {
                        
        // L'entite possède-t-elle des filles
        $entiteFille = $this->getFille($id_e);
        if ($entiteFille) {
            throw new UnrecoverableException("Suppression impossible : l'entité {id_e=$id_e} possède des entités filles");
        }
            
        // Des documents sont-ils définis sur l'entité
        $sql = "SELECT id_e FROM document_entite where id_e=?";
        $documentSurEntite = $this->queryOne($sql, $id_e);
        if ($documentSurEntite) {
            throw new UnrecoverableException("Suppression impossible : des documents sont définis sur l'entité {id_e=$id_e}");
        }
            
        // Des utilisateurs sont-ils définis sur l'entité
        $sql = "SELECT id_e FROM utilisateur where id_e=?";
        $utilisateurSurEntite = $this->queryOne($sql, $id_e);
        if ($utilisateurSurEntite) {
            throw new UnrecoverableException("Suppression impossible : des utilisateurs sont définis sur l'entité {id_e=$id_e}");
        }
            
        // Des connecteurs sont-ils définis sur l'entité
        $sql = "SELECT id_e FROM connecteur_entite where id_e=?";
        $connecteurSurEntite = $this->queryOne($sql, $id_e);
        if ($connecteurSurEntite) {
            throw new UnrecoverableException("Suppression impossible : des connecteurs sont définis sur l'entité {id_e=$id_e}");
        }
                        
        $this->deleteEntite($id_e);
    }
        
    private function deleteEntite($id_e)
    {
        // Suppression de l'ancetre entité
        $sql = "DELETE FROM entite_ancetre where id_e=?";
        $this->query($sql, $id_e);
        // Suppression de l'entité
        $sql = "DELETE FROM entite WHERE id_e=?";
        $this->query($sql, $id_e);
    }
        
    public function delete($id_e)
    {
        $this->deleteEntite($id_e);
    }
        
    public function updateEntiteMere($id_e, $id_entite_mere)
    {
        $sql = "UPDATE entite SET entite_mere = ? WHERE id_e = ?";
        return $this->query($sql, $id_entite_mere, $id_e);
    }

    public function setActive($id_e, $active)
    {
        $sql = "UPDATE entite SET is_active=? WHERE id_e=?";
        $this->query($sql, $active, $id_e);
    }
            
    public function getInfoByDenomination($denomination)
    {
        $sql = "SELECT * FROM entite WHERE denomination=?";
        return $this->queryOne($sql, $denomination);
    }
    
    public function getNumberOfEntiteWithName($denomination)
    {
        $sql = "SELECT count(*) FROM entite WHERE denomination=?";
        return $this->queryOne($sql, $denomination);
    }


    /**
     * @param array $data Données en vrac dans un tableau contenant ou un id_e ou une dénomination
     * @return mixed Toutes les informations de l'entité trouvée
     * @throws Exception
     */
    public function getEntiteFromData(array $data)
    {

        if (! empty($data['id_e'])) {
            $id_e = $data['id_e'];
            $infoEntiteExistante = $this->getInfo($id_e);
            if (! $infoEntiteExistante) {
                throw new Exception("L'identifiant de l'entite n'existe pas : {id_e=$id_e}");
            }
            return $infoEntiteExistante;
        }

        if (! empty($data['denomination'])) {
            $denomination = $data['denomination'];

            $numberOfEntite = $this->getNumberOfEntiteWithName($denomination);

            if ($numberOfEntite == 0) {
                throw new Exception("La dénomination de l'entité n'existe pas : {denomination=$denomination}");
            }

            if ($numberOfEntite > 1) {
                throw new Exception("Plusieurs entités portent le même nom, préférez utiliser son identifiant");
            }


            return $this->getInfoByDenomination($denomination);
        }

        throw new Exception("Aucun paramètre permettant la recherche de l'entité n'a été renseigné");
    }
}
