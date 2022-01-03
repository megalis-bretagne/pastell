<?php

class AdminControler extends Controler
{
    /** @return UtilisateurCreator */
    private function getUtilisateurCreator()
    {
        return $this->getInstance('UtilisateurCreator');
    }

    /** @return RoleDroit */
    private function getRoleDroit()
    {
        return $this->getInstance('RoleDroit');
    }

    /** @return RoleSQL */
    private function getRoleSQL()
    {
        return $this->getInstance('RoleSQL');
    }

    /** @return UtilisateurSQL */
    private function getUtilisateur()
    {
        return $this->getInstance('Utilisateur');
    }

    /** @return RoleUtilisateur */
    private function getRoleUtilisateur()
    {
        return $this->getInstance('RoleUtilisateur');
    }

    /** @return EntiteCreator */
    private function getEntiteCreator()
    {
        return $this->getInstance('EntiteCreator');
    }

    public function createAdmin($login, $password, $email)
    {
        $this->fixDroit();

        $id_u = $this->getUtilisateurCreator()->create($login, $password, $password, $email);
        if (!$id_u) {
            $this->setLastError($this->getUtilisateurCreator()->getLastError());
            return false;
        }
        //Ajout de l'affectation du nom (reprise du login) pour avoir accès à la fiche de l'utilisateur depuis l'IHM
        $this->getUtilisateur()->setNomPrenom($id_u, $login, "");
        $this->getUtilisateur()->validMailAuto($id_u);
        $this->getUtilisateur()->setColBase($id_u, 0);
        $this->getRoleUtilisateur()->addRole($id_u, "admin", 0);
        return true;
    }

    public function fixDroit()
    {
        $this->getRoleSQL()->edit("admin", "Administrateur");

        foreach ($this->getRoleDroit()->getAllDroit() as $droit) {
            $this->getRoleSQL()->addDroit("admin", $droit);
        }
        $this->getEntiteCreator()->updateAllEntiteAncetre();
    }

    public function createOrUpdateAdmin(UtilisateurObject $utilisateurObject, Closure $function_log)
    {
        $utilisateur = $this->getUtilisateur();
        $utilisateur_info = $utilisateur->getInfoByLogin($utilisateurObject->login);
        if (! $utilisateur_info) {
            $function_log("L'utilisateur {$utilisateurObject->login} n'existe pas.");
            $create_admin_result = $this->createAdmin(
                $utilisateurObject->login,
                $utilisateurObject->password,
                $utilisateurObject->email
            );
            if (! $create_admin_result) {
                $function_log("Erreur lors de la création de l'utilisateur :  " . $this->getLastError()->getLastError());
                return;
            }
            $function_log("Création de l'utilisateur {$utilisateurObject->login} OK");
            return;
        }
        $function_log("L'utilisateur {$utilisateurObject->login} existe déjà");
        $this->getUtilisateur()->setPassword($utilisateur_info['id_u'], $utilisateurObject->password);
        $this->getUtilisateur()->setEmail($utilisateur_info['id_u'], $utilisateurObject->email);
        $function_log("Mise à jour de l'utilisateur {$utilisateurObject->login}.");
        return;
    }
}
